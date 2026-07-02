<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\Comentario;
use App\Models\ItemControle;
use App\Models\ItemControleChecklist;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnitEnum;

class Projetos extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-folder';

    protected static string | UnitEnum | null $navigationGroup = 'Operação';

    protected static ?string $navigationLabel = 'Projetos';

    protected static ?string $title = 'Projetos';

    protected static ?int $navigationSort = 11;

    protected string $view = 'filament.pages.projetos';

    public string $busca = '';

    public string $filtroStatus = 'todos';

    public string $filtroPrioridade = 'todos';

    public ?string $projetoSelecionadoTipo = null;

    public ?int $itemSelecionadoId = null;

    public string $novoComentario = '';

    public string $novaEtapa = '';

    protected function baseQuery(): Builder
    {
        return ItemControle::query()
            ->with([
                'empresa:id,razao_social',
                'responsavel:id,nome',
                'checklists:id,item_controle_id,titulo,concluido,ordem,concluido_em',
            ])
            ->withCount([
                'checklists as checklist_total',
                'checklists as checklist_concluidos' => fn (Builder $query) => $query->where('concluido', true),
                'comentarios as comentarios_total',
            ])
            ->visibleForUser(Filament::auth()->user());
    }

    protected function filteredQuery(): Builder
    {
        return $this->baseQuery()
            ->when(filled($this->busca), function (Builder $query): void {
                $busca = trim($this->busca);

                $query->where(function (Builder $subQuery) use ($busca): void {
                    $subQuery->where('titulo', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%")
                        ->orWhere('tipo', 'like', "%{$busca}%")
                        ->orWhereHas('empresa', fn (Builder $empresaQuery) => $empresaQuery->where('razao_social', 'like', "%{$busca}%"))
                        ->orWhereHas('responsavel', fn (Builder $responsavelQuery) => $responsavelQuery->where('nome', 'like', "%{$busca}%"));
                });
            })
            ->when($this->filtroPrioridade !== 'todos', fn (Builder $query) => $query->where('prioridade', $this->filtroPrioridade))
            ->when($this->filtroStatus !== 'todos', function (Builder $query): void {
                if ($this->filtroStatus === 'atrasados') {
                    $query->whereNotIn('status', ['concluido', 'concluida', 'cancelado'])
                        ->whereDate('data_vencimento', '<', now()->toDateString());

                    return;
                }

                if ($this->filtroStatus === 'em_andamento') {
                    $query->whereIn('status', ['em_andamento', 'andamento']);

                    return;
                }

                if ($this->filtroStatus === 'concluidos') {
                    $query->whereIn('status', ['concluido', 'concluida']);

                    return;
                }

                $query->where('status', $this->filtroStatus);
            });
    }

    public function limparFiltros(): void
    {
        $this->busca = '';
        $this->filtroStatus = 'todos';
        $this->filtroPrioridade = 'todos';
    }

    public function abrirProjeto(string $tipo): void
    {
        $this->projetoSelecionadoTipo = $tipo;
        $this->itemSelecionadoId = null;
        $this->novoComentario = '';
        $this->novaEtapa = '';
    }

    public function fecharProjeto(): void
    {
        $this->projetoSelecionadoTipo = null;
        $this->itemSelecionadoId = null;
        $this->novoComentario = '';
        $this->novaEtapa = '';
    }

    public function selecionarItem(int $itemId): void
    {
        $this->itemSelecionadoId = $itemId;
        $this->novoComentario = '';
        $this->novaEtapa = '';
    }

    public function alterarStatusItem(int $itemId, string $status): void
    {
        $item = $this->baseQuery()->whereKey($itemId)->first();

        if (! $item) {
            Notification::make()->title('Item não encontrado.')->danger()->send();

            return;
        }

        $dados = ['status' => $status];

        if (in_array($status, ['concluido', 'concluida'], true)) {
            $dados['data_conclusao'] = now()->toDateString();
        } elseif ($item->data_conclusao) {
            $dados['data_conclusao'] = null;
        }

        $item->update($dados);

        Notification::make()->title('Status atualizado com sucesso.')->success()->send();
    }

    public function adicionarComentario(): void
    {
        $comentario = trim($this->novoComentario);

        if (! $this->itemSelecionadoId || blank($comentario)) {
            Notification::make()->title('Selecione um item e escreva um comentário.')->warning()->send();

            return;
        }

        $item = $this->baseQuery()->whereKey($this->itemSelecionadoId)->first();

        if (! $item) {
            Notification::make()->title('Item não encontrado.')->danger()->send();

            return;
        }

        Comentario::query()->create([
            'item_controle_id' => $item->id,
            'user_id' => Filament::auth()->id(),
            'comentario' => $comentario,
        ]);

        $this->novoComentario = '';

        Notification::make()->title('Comentário adicionado.')->success()->send();
    }

    public function adicionarEtapa(): void
    {
        $titulo = trim($this->novaEtapa);

        if (! $this->itemSelecionadoId || blank($titulo)) {
            Notification::make()->title('Selecione um item e escreva a etapa.')->warning()->send();

            return;
        }

        $item = $this->baseQuery()->whereKey($this->itemSelecionadoId)->first();

        if (! $item) {
            Notification::make()->title('Item não encontrado.')->danger()->send();

            return;
        }

        $ordem = ((int) ItemControleChecklist::query()
            ->where('item_controle_id', $item->id)
            ->max('ordem')) + 1;

        ItemControleChecklist::query()->create([
            'item_controle_id' => $item->id,
            'titulo' => $titulo,
            'concluido' => false,
            'ordem' => $ordem,
        ]);

        $this->novaEtapa = '';

        Notification::make()->title('Etapa adicionada.')->success()->send();
    }

    public function alternarChecklist(int $checklistId): void
    {
        $checklist = ItemControleChecklist::query()
            ->whereHas('itemControle', fn (Builder $query) => $query->visibleForUser(Filament::auth()->user()))
            ->whereKey($checklistId)
            ->first();

        if (! $checklist) {
            Notification::make()->title('Checklist não encontrado.')->danger()->send();

            return;
        }

        if ($checklist->concluido) {
            $checklist->marcarComoPendente();
        } else {
            $checklist->marcarComoConcluido(Filament::auth()->user());
        }

        Notification::make()->title('Checklist atualizado.')->success()->send();
    }

    public function getResumo(): array
    {
        $query = $this->filteredQuery();

        return [
            'total' => (clone $query)->count(),
            'ativos' => (clone $query)->whereNotIn('status', ['concluido', 'concluida', 'cancelado'])->count(),
            'atrasados' => (clone $query)
                ->whereNotIn('status', ['concluido', 'concluida', 'cancelado'])
                ->whereDate('data_vencimento', '<', now()->toDateString())
                ->count(),
            'hoje' => (clone $query)
                ->whereNotIn('status', ['concluido', 'concluida', 'cancelado'])
                ->whereDate('data_vencimento', now()->toDateString())
                ->count(),
            'concluidos' => (clone $query)->whereIn('status', ['concluido', 'concluida'])->count(),
        ];
    }

    public function getProjetos(): array
    {
        $itens = $this->filteredQuery()
            ->orderByRaw('data_vencimento IS NULL, data_vencimento ASC')
            ->latest('updated_at')
            ->get()
            ->groupBy(fn (ItemControle $item): string => filled($item->tipo) ? (string) $item->tipo : 'sem_tipo');

        return $itens->map(function (Collection $grupo, string $tipo): array {
            $total = $grupo->count();
            $concluidos = $grupo->filter(fn (ItemControle $item): bool => in_array($item->status, ['concluido', 'concluida'], true))->count();
            $atrasados = $grupo->filter(fn (ItemControle $item): bool => $this->itemEstaAtrasado($item))->count();

            $proximaEntrega = $grupo
                ->filter(fn (ItemControle $item): bool => ! in_array($item->status, ['concluido', 'concluida', 'cancelado'], true) && filled($item->data_vencimento))
                ->sortBy('data_vencimento')
                ->first();

            $checklistTotal = (int) $grupo->sum('checklist_total');
            $checklistConcluidos = (int) $grupo->sum('checklist_concluidos');

            $percentual = $total > 0 ? (int) round(($concluidos / $total) * 100) : 0;
            $checklistPercentual = $checklistTotal > 0 ? (int) round(($checklistConcluidos / $checklistTotal) * 100) : 0;

            return [
                'tipo' => $tipo,
                'nome' => $this->formatarTexto($tipo),
                'total' => $total,
                'ativos' => $total - $concluidos,
                'concluidos' => $concluidos,
                'atrasados' => $atrasados,
                'percentual' => $percentual,
                'checklist_percentual' => $checklistPercentual,
                'comentarios' => (int) $grupo->sum('comentarios_total'),
                'proxima_entrega' => $proximaEntrega?->data_vencimento?->format('d/m/Y') ?? 'Sem prazo',
                'responsaveis' => $grupo->pluck('responsavel.nome')->filter()->unique()->take(3)->implode(', ') ?: 'Sem responsável',
                'empresas' => $grupo->pluck('empresa.razao_social')->filter()->unique()->take(3)->implode(', ') ?: 'Sem empresa',
                'itens' => $grupo->take(4)->map(fn (ItemControle $item): array => $this->mapItem($item))->values()->all(),
            ];
        })
            ->sortByDesc('atrasados')
            ->sortByDesc('ativos')
            ->values()
            ->all();
    }

    public function getProjetoSelecionado(): ?array
    {
        if (! $this->projetoSelecionadoTipo) {
            return null;
        }

        $itens = $this->baseQuery()
            ->when($this->projetoSelecionadoTipo === 'sem_tipo', function (Builder $query): void {
                $query->where(function (Builder $subQuery): void {
                    $subQuery->whereNull('tipo')->orWhere('tipo', '');
                });
            }, function (Builder $query): void {
                $query->where('tipo', $this->projetoSelecionadoTipo);
            })
            ->orderByRaw('data_vencimento IS NULL, data_vencimento ASC')
            ->latest('updated_at')
            ->get();

        if ($itens->isEmpty()) {
            return null;
        }

        $itemSelecionado = $this->itemSelecionadoId
            ? $itens->firstWhere('id', $this->itemSelecionadoId)
            : $itens->first();

        if (! $itemSelecionado) {
            $itemSelecionado = $itens->first();
            $this->itemSelecionadoId = $itemSelecionado?->id;
        }

        $total = $itens->count();
        $concluidos = $itens->filter(fn (ItemControle $item): bool => in_array($item->status, ['concluido', 'concluida'], true))->count();

        return [
            'tipo' => $this->projetoSelecionadoTipo,
            'nome' => $this->formatarTexto($this->projetoSelecionadoTipo),
            'total' => $total,
            'concluidos' => $concluidos,
            'atrasados' => $itens->filter(fn (ItemControle $item): bool => $this->itemEstaAtrasado($item))->count(),
            'percentual' => $total > 0 ? (int) round(($concluidos / $total) * 100) : 0,
            'itens' => $itens->map(fn (ItemControle $item): array => $this->mapItem($item))->values()->all(),
            'item_selecionado' => $itemSelecionado ? $this->mapItemDetalhado($itemSelecionado) : null,
        ];
    }

    public function getRecentes(): array
    {
        return $this->filteredQuery()
            ->latest('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->mapItem($item))
            ->all();
    }

    public function getUrlNovaTarefa(): string
    {
        return ItemControleResource::getUrl('create');
    }

    public function getUrlTarefas(): string
    {
        return ItemControleResource::getUrl('index');
    }

    protected function mapItem(ItemControle $item): array
    {
        $checklistTotal = (int) ($item->checklist_total ?? $item->checklists->count());
        $checklistConcluidos = (int) ($item->checklist_concluidos ?? $item->checklists->where('concluido', true)->count());

        return [
            'id' => $item->id,
            'titulo' => $item->titulo,
            'descricao' => Str::limit((string) $item->descricao, 120),
            'tipo' => $this->formatarTexto((string) $item->tipo),
            'status_original' => (string) $item->status,
            'status' => $this->formatarTexto((string) $item->status),
            'prioridade_original' => (string) $item->prioridade,
            'prioridade' => $this->formatarTexto((string) $item->prioridade),
            'empresa' => $item->empresa?->razao_social ?? '-',
            'responsavel' => $item->responsavel?->nome ?? '-',
            'vencimento' => $item->data_vencimento?->format('d/m/Y') ?? 'Sem prazo',
            'atrasado' => $this->itemEstaAtrasado($item),
            'checklist_total' => $checklistTotal,
            'checklist_concluidos' => $checklistConcluidos,
            'checklist_percentual' => $checklistTotal > 0 ? (int) round(($checklistConcluidos / $checklistTotal) * 100) : 0,
            'comentarios_total' => (int) ($item->comentarios_total ?? 0),
            'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
        ];
    }

    protected function mapItemDetalhado(ItemControle $item): array
    {
        $item->loadMissing([
            'checklists:id,item_controle_id,titulo,concluido,ordem,concluido_em',
            'comentarios:id,item_controle_id,user_id,comentario,created_at',
            'comentarios.user:id,name',
        ]);

        return array_merge($this->mapItem($item), [
            'descricao_completa' => $item->descricao ?: 'Sem descrição cadastrada.',
            'observacao' => $item->observacao ?: 'Sem observação cadastrada.',
            'data_conclusao' => $item->data_conclusao?->format('d/m/Y') ?? '-',
            'checklists' => $item->checklists->sortBy('ordem')->map(fn (ItemControleChecklist $checklist): array => [
                'id' => $checklist->id,
                'titulo' => $checklist->titulo,
                'concluido' => (bool) $checklist->concluido,
            ])->values()->all(),
            'comentarios' => $item->comentarios->sortByDesc('created_at')->take(5)->map(fn (Comentario $comentario): array => [
                'autor' => $comentario->user?->name ?? 'Sistema',
                'comentario' => $comentario->comentario,
                'data' => $comentario->created_at?->format('d/m/Y H:i') ?? '-',
            ])->values()->all(),
        ]);
    }

    protected function itemEstaAtrasado(ItemControle $item): bool
    {
        return ! in_array($item->status, ['concluido', 'concluida', 'cancelado'], true)
            && filled($item->data_vencimento)
            && $item->data_vencimento->lt(now()->startOfDay());
    }

    protected function formatarTexto(?string $texto): string
    {
        $texto = filled($texto) ? $texto : 'sem_tipo';

        return Str::of($texto)
            ->replace(['_', '-'], ' ')
            ->lower()
            ->title()
            ->toString();
    }
}