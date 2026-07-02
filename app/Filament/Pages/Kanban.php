<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use App\Models\ItemControleChecklist;
use App\Models\ItemControleComentario;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

class Kanban extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-view-columns';

    protected static string | UnitEnum | null $navigationGroup = 'Operação';

    protected static ?string $navigationLabel = 'Kanban';

    protected static ?string $title = 'Kanban';

    protected static ?int $navigationSort = 40;

    protected string $view = 'filament.pages.kanban';

    public ?int $itemSelecionadoId = null;

    public string $novoComentario = '';

    public string $novoChecklistTitulo = '';

    protected function baseQuery(): Builder
    {
        return ItemControle::query()
            ->select([
                'id',
                'titulo',
                'descricao',
                'observacao',
                'tipo',
                'status',
                'prioridade',
                'empresa_id',
                'responsavel_id',
                'data_vencimento',
                'data_conclusao',
                'sla_horas',
                'sla_limite_em',
                'sla_concluido_em',
                'sla_status',
                'created_at',
                'updated_at',
            ])
            ->with([
                'empresa:id,razao_social',
                'responsavel:id,nome',
            ])
            ->withCount([
                'checklists',
                'checklists as checklists_concluidos_count' => fn (Builder $query): Builder => $query->where('concluido', true),
                'comentariosKanban as comentarios_total',
            ])
            ->visibleForUser(Filament::auth()->user());
    }

    protected function detalheQuery(): Builder
    {
        return $this->baseQuery()
            ->with([
                'checklists:id,item_controle_id,titulo,concluido,concluido_em,concluido_por,ordem',
                'comentariosKanban:id,item_controle_id,user_id,comentario,created_at',
                'comentariosKanban.user:id,name',
            ]);
    }

    public function getColunas(): array
    {
        $colunas = [
            'pendente' => 'Pendente',
            'em_andamento' => 'Em andamento',
            'concluido' => 'Concluído',
            'vencido' => 'Vencido',
        ];

        return collect($colunas)->map(function (string $label, string $status): array {
            $query = $this->baseQuery();

            if ($status === 'vencido') {
                $query->whereNotIn('status', ['concluido', 'concluida', 'cancelado'])
                    ->whereDate('data_vencimento', '<', now()->toDateString());
            } elseif ($status === 'em_andamento') {
                $query->whereIn('status', ['em_andamento', 'andamento']);
            } elseif ($status === 'concluido') {
                $query->whereIn('status', ['concluido', 'concluida']);
            } else {
                $query->where('status', 'pendente');
            }

            $total = (clone $query)->count();

            $itens = $query->orderByRaw('data_vencimento IS NULL, data_vencimento ASC')
                ->latest('updated_at')
                ->limit(15)
                ->get()
                ->map(fn (ItemControle $item): array => $this->formatarItemKanban($item))
                ->all();

            return [
                'status' => $status,
                'label' => $label,
                'total' => $total,
                'itens' => $itens,
            ];
        })->values()->all();
    }

    protected function formatarItemKanban(ItemControle $item): array
    {
        $totalChecklist = (int) ($item->checklists_count ?? ($item->relationLoaded('checklists') ? $item->checklists->count() : 0));
        $checklistsConcluidos = (int) ($item->checklists_concluidos_count ?? ($item->relationLoaded('checklists') ? $item->checklists->where('concluido', true)->count() : 0));
        $percentualChecklist = $totalChecklist > 0 ? round(($checklistsConcluidos / $totalChecklist) * 100) : 0;
        $vencido = filled($item->data_vencimento)
            && ! in_array($item->status, ['concluido', 'concluida', 'cancelado'], true)
            && $item->data_vencimento->lt(now()->startOfDay());

        return [
            'id' => $item->id,
            'titulo' => $item->titulo,
            'descricao' => Str::limit(strip_tags((string) $item->descricao), 140),
            'tipo' => ucfirst(str_replace('_', ' ', (string) $item->tipo)),
            'status' => ucfirst(str_replace('_', ' ', (string) $item->status)),
            'status_raw' => (string) $item->status,
            'prioridade' => ucfirst((string) $item->prioridade),
            'empresa' => $item->empresa?->razao_social ?? '-',
            'responsavel' => $item->responsavel?->nome ?? '-',
            'vencimento' => $item->data_vencimento?->format('d/m/Y') ?? '-',
            'vencido' => $vencido,
            'comentarios' => (int) ($item->comentarios_total ?? ($item->relationLoaded('comentariosKanban') ? $item->comentariosKanban->count() : 0)),
            'checklists_total' => $totalChecklist,
            'checklists_concluidos' => $checklistsConcluidos,
            'checklists_percentual' => $percentualChecklist,
            'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
        ];
    }

    public function abrirItem(int $itemId): void
    {
        $this->itemSelecionadoId = $itemId;
        $this->novoComentario = '';
        $this->novoChecklistTitulo = '';
    }

    public function fecharItem(): void
    {
        $this->itemSelecionadoId = null;
        $this->novoComentario = '';
        $this->novoChecklistTitulo = '';
    }

    public function getItemSelecionado(): ?array
    {
        if (! $this->itemSelecionadoId) {
            return null;
        }

        $item = $this->detalheQuery()->find($this->itemSelecionadoId);

        if (! $item) {
            $this->fecharItem();
            return null;
        }

        $dados = $this->formatarItemKanban($item);

        $dados['observacao'] = filled($item->observacao) ? (string) $item->observacao : null;
        $dados['descricao_completa'] = filled($item->descricao) ? (string) $item->descricao : null;
        $dados['data_criacao'] = $item->created_at?->format('d/m/Y H:i') ?? '-';
        $dados['data_atualizacao'] = $item->updated_at?->format('d/m/Y H:i') ?? '-';
        $dados['sla'] = $item->sla_limite_em?->format('d/m/Y H:i') ?? ($item->sla_horas ? $item->sla_horas . ' horas' : '-');
        $dados['checklists'] = $item->checklists->map(fn (ItemControleChecklist $checklist): array => [
            'id' => $checklist->id,
            'titulo' => $checklist->titulo,
            'concluido' => (bool) $checklist->concluido,
            'concluido_em' => $checklist->concluido_em?->format('d/m/Y H:i'),
        ])->values()->all();
        $dados['comentarios_lista'] = $item->comentariosKanban
            ->sortByDesc('created_at')
            ->take(6)
            ->map(fn (ItemControleComentario $comentario): array => [
                'autor' => $comentario->user?->name ?? 'Sistema',
                'comentario' => $comentario->comentario,
                'data' => $comentario->created_at?->format('d/m/Y H:i') ?? '-',
            ])
            ->values()
            ->all();

        return $dados;
    }

    public function moverItemKanban(int $itemId, string $status): void
    {
        $this->atualizarStatus($itemId, $status, true);
    }

    public function atualizarStatus(int $itemId, string $status, bool $silencioso = false): void
    {
        $statusPermitidos = ['pendente', 'em_andamento', 'concluido'];

        if (! in_array($status, $statusPermitidos, true)) {
            return;
        }

        $item = $this->baseQuery()->find($itemId);

        if (! $item) {
            return;
        }

        $item->update([
            'status' => $status,
            'data_conclusao' => $status === 'concluido' ? now()->toDateString() : null,
            'sla_concluido_em' => $status === 'concluido' ? now() : null,
            'sla_status' => $status === 'concluido' ? 'concluido' : $item->sla_status,
        ]);

        if (! $silencioso) {
            Notification::make()
                ->title('Status atualizado com sucesso')
                ->success()
                ->send();
        }
    }

    public function alternarChecklist(int $checklistId): void
    {
        $checklist = ItemControleChecklist::query()
            ->whereHas('itemControle', fn (Builder $query) => $query->visibleForUser(Filament::auth()->user()))
            ->find($checklistId);

        if (! $checklist) {
            return;
        }

        if ($checklist->concluido) {
            $checklist->marcarComoPendente();
        } else {
            $checklist->marcarComoConcluido(Filament::auth()->user());
        }

        Notification::make()
            ->title('Checklist atualizado')
            ->success()
            ->send();
    }

    public function adicionarChecklist(): void
    {
        $titulo = trim($this->novoChecklistTitulo);

        if (! $this->itemSelecionadoId || $titulo === '') {
            return;
        }

        $item = $this->baseQuery()->find($this->itemSelecionadoId);

        if (! $item) {
            return;
        }

        ItemControleChecklist::query()->create([
            'item_controle_id' => $item->id,
            'titulo' => $titulo,
            'concluido' => false,
            'ordem' => ((int) $item->checklists()->max('ordem')) + 1,
        ]);

        $this->novoChecklistTitulo = '';

        Notification::make()
            ->title('Checklist adicionado')
            ->success()
            ->send();
    }

    public function adicionarComentario(): void
    {
        $comentario = trim($this->novoComentario);

        if (! $this->itemSelecionadoId || $comentario === '') {
            return;
        }

        $item = $this->baseQuery()->find($this->itemSelecionadoId);

        if (! $item) {
            return;
        }

        ItemControleComentario::query()->create([
            'item_controle_id' => $item->id,
            'user_id' => Filament::auth()->id(),
            'comentario' => $comentario,
        ]);

        $this->novoComentario = '';

        Notification::make()
            ->title('Comentário adicionado')
            ->success()
            ->send();
    }

    public function getUrlNovaTarefa(): string
    {
        return ItemControleResource::getUrl('create');
    }
}
