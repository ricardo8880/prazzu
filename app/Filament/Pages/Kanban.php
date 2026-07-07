<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use App\Models\ItemControleChecklist;
use App\Models\ItemControleComentario;
use App\Support\PrazzuAccessControl;
use App\Services\ItemControleOperationalService;
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

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return PrazzuAccessControl::canAccessPage('tarefas.view');
    }

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
                'categoria_id',
                'status',
                'prioridade',
                'urgencia',
                'document_status',
                'portal_status',
                'approval_required',
                'approval_status',
                'bloqueado',
                'blocked_by_dependency',
                'risco_score',
                'risk_score',
                'estimated_minutes',
                'actual_minutes',
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
                'categoria:id,nome',
                'tags:id,nome',
            ])
            ->withCount([
                'checklists',
                'checklists as checklists_concluidos_count' => fn (Builder $query): Builder => $query->where('concluido', true),
                'comentariosKanban as comentarios_total',
                'anexos as anexos_total',
                'documentVersions as versoes_total',
                'clientPortalMessages as mensagens_portal_total',
                'dependencies as dependencias_total',
                'blockers as bloqueios_total',
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
                'anexos:id,item_controle_id,nome_original,arquivo,created_at',
                'documentVersions:id,item_controle_id,version_number,status,created_at',
                'clientPortalMessages:id,item_controle_id,user_id,sender_type,message,created_at',
                'dependencies:id,item_controle_id,depends_on_item_controle_id,type,notes,blocked_until_resolved',
                'dependencies.dependsOnItem:id,titulo,status,data_vencimento',
                'blockers:id,item_controle_id,depends_on_item_controle_id,type,notes,blocked_until_resolved',
                'blockers.itemControle:id,titulo,status,data_vencimento',
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
            'tipo' => $this->formatarRotulo($item->tipo),
            'categoria' => $item->categoria?->nome ?? '-',
            'status' => $this->formatarRotulo($item->status),
            'status_raw' => (string) $item->status,
            'status_normalized' => $this->normalizarStatus((string) $item->status),
            'prioridade' => $this->formatarRotulo($item->prioridade),
            'prioridade_raw' => (string) ($item->prioridade ?: 'media'),
            'urgencia' => $this->formatarRotulo($item->urgencia ?: 'media'),
            'empresa' => $item->empresa?->razao_social ?? '-',
            'responsavel' => $item->responsavel?->nome ?? '-',
            'vencimento' => $item->data_vencimento?->format('d/m/Y') ?? '-',
            'dias_para_vencer' => $this->calcularDiasParaVencer($item),
            'vencido' => $vencido,
            'bloqueado' => (bool) ($item->bloqueado || $item->blocked_by_dependency),
            'risco_score' => (int) ($item->risco_score ?? $item->risk_score ?? 0),
            'comentarios' => (int) ($item->comentarios_total ?? ($item->relationLoaded('comentariosKanban') ? $item->comentariosKanban->count() : 0)),
            'anexos' => (int) ($item->anexos_total ?? ($item->relationLoaded('anexos') ? $item->anexos->count() : 0)),
            'versoes' => (int) ($item->versoes_total ?? ($item->relationLoaded('documentVersions') ? $item->documentVersions->count() : 0)),
            'mensagens_portal' => (int) ($item->mensagens_portal_total ?? ($item->relationLoaded('clientPortalMessages') ? $item->clientPortalMessages->count() : 0)),
            'dependencias' => (int) ($item->dependencias_total ?? ($item->relationLoaded('dependencies') ? $item->dependencies->count() : 0)),
            'bloqueios' => (int) ($item->bloqueios_total ?? ($item->relationLoaded('blockers') ? $item->blockers->count() : 0)),
            'document_status' => $this->formatarRotulo($item->document_status ?: 'nao_informado'),
            'portal_status' => $this->formatarRotulo($item->portal_status ?: 'nao_informado'),
            'approval_status' => $this->formatarRotulo($item->approval_status ?: 'nao_exige'),
            'approval_required' => (bool) $item->approval_required,
            'tempo_estimado' => $this->formatarMinutos($item->estimated_minutes),
            'tempo_real' => $this->formatarMinutos($item->actual_minutes),
            'checklists_total' => $totalChecklist,
            'checklists_concluidos' => $checklistsConcluidos,
            'checklists_percentual' => $percentualChecklist,
            'tags' => $item->relationLoaded('tags') ? $item->tags->pluck('nome')->take(5)->values()->all() : [],
            'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
        ];
    }


    protected function formatarRotulo(mixed $valor): string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return '-';
        }

        return Str::of($valor)
            ->replace(['_', '-'], ' ')
            ->lower()
            ->title()
            ->toString();
    }

    protected function normalizarStatus(string $status): string
    {
        return match ($status) {
            'concluida' => 'concluido',
            'andamento' => 'em_andamento',
            default => $status,
        };
    }

    protected function calcularDiasParaVencer(ItemControle $item): ?int
    {
        if (! $item->data_vencimento) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($item->data_vencimento->copy()->startOfDay(), false);
    }

    protected function formatarMinutos(mixed $minutos): string
    {
        $minutos = (int) $minutos;

        if ($minutos <= 0) {
            return '-';
        }

        if ($minutos < 60) {
            return $minutos . ' min';
        }

        $horas = intdiv($minutos, 60);
        $restante = $minutos % 60;

        return $restante > 0 ? $horas . 'h ' . $restante . 'min' : $horas . 'h';
    }

    protected function definirProximaAcao(array $dados): array
    {
        if ($dados['bloqueado']) {
            return [
                'tom' => 'danger',
                'titulo' => 'Resolver bloqueio antes de avançar',
                'descricao' => 'Existe dependência ou bloqueio operacional impedindo a execução segura deste item.',
            ];
        }

        if ($dados['vencido']) {
            return [
                'tom' => 'danger',
                'titulo' => 'Priorizar regularização hoje',
                'descricao' => 'O prazo já passou. Atualize o status, registre o impedimento ou conclua a pendência.',
            ];
        }

        if (($dados['checklists_total'] ?? 0) > 0 && ($dados['checklists_percentual'] ?? 0) < 100) {
            return [
                'tom' => 'warning',
                'titulo' => 'Executar a próxima etapa do checklist',
                'descricao' => 'Use o checklist para deixar claro o que falta fazer e evitar retrabalho.',
            ];
        }

        if (($dados['approval_required'] ?? false) && ! in_array(strtolower((string) ($dados['approval_status'] ?? '')), ['aprovado', 'approved'], true)) {
            return [
                'tom' => 'warning',
                'titulo' => 'Acompanhar aprovação',
                'descricao' => 'Este item exige aprovação. Verifique se o responsável já validou antes de concluir.',
            ];
        }

        if (($dados['anexos'] ?? 0) === 0 && in_array(strtolower((string) ($dados['document_status'] ?? '')), ['solicitado', 'pendente'], true)) {
            return [
                'tom' => 'warning',
                'titulo' => 'Solicitar ou anexar documento',
                'descricao' => 'Há sinal de documento pendente. Centralize o arquivo para manter o histórico completo.',
            ];
        }

        return [
            'tom' => 'success',
            'titulo' => 'Fluxo sem bloqueio crítico',
            'descricao' => 'Revise os detalhes, mova o card para o próximo status ou registre um comentário se necessário.',
        ];
    }

    protected function montarAlertasOperacionais(array $dados): array
    {
        $alertas = [];

        if ($dados['bloqueado']) {
            $alertas[] = ['tom' => 'danger', 'texto' => 'Bloqueado por dependência ou impedimento operacional.'];
        }

        if ($dados['vencido']) {
            $alertas[] = ['tom' => 'danger', 'texto' => 'Prazo vencido. Deve aparecer como prioridade do dia.'];
        } elseif (($dados['dias_para_vencer'] ?? null) !== null && $dados['dias_para_vencer'] <= 2) {
            $alertas[] = ['tom' => 'warning', 'texto' => 'Vence em até 2 dias. Bom acompanhar de perto.'];
        }

        if (($dados['risco_score'] ?? 0) >= 70) {
            $alertas[] = ['tom' => 'danger', 'texto' => 'Risco operacional alto. Evite concluir sem validação.'];
        }

        if (($dados['dependencias'] ?? 0) > 0) {
            $alertas[] = ['tom' => 'warning', 'texto' => 'Possui dependências relacionadas. Confira antes de mover para concluído.'];
        }

        if (($dados['approval_required'] ?? false)) {
            $alertas[] = ['tom' => 'info', 'texto' => 'Exige aprovação antes do encerramento definitivo.'];
        }

        return $alertas;
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
        $dados['sla_status'] = $this->formatarRotulo($item->sla_status ?: 'nao_informado');
        $dados['proxima_acao'] = $this->definirProximaAcao($dados);
        $dados['alertas_operacionais'] = $this->montarAlertasOperacionais($dados);
        $dados['anexos_lista'] = $item->anexos
            ->take(4)
            ->map(fn ($anexo): array => [
                'nome' => $anexo->nome_original ?: basename((string) $anexo->arquivo),
                'data' => $anexo->created_at?->format('d/m/Y H:i') ?? '-',
            ])
            ->values()
            ->all();
        $dados['versoes_lista'] = $item->documentVersions
            ->take(3)
            ->map(fn ($versao): array => [
                'numero' => $versao->version_number,
                'status' => $this->formatarRotulo($versao->status ?: 'registrada'),
                'data' => $versao->created_at?->format('d/m/Y H:i') ?? '-',
            ])
            ->values()
            ->all();
        $dados['dependencias_lista'] = $item->dependencies
            ->take(4)
            ->map(fn ($dependencia): array => [
                'titulo' => $dependencia->dependsOnItem?->titulo ?? 'Dependência operacional',
                'status' => $dependencia->blocked_until_resolved ? 'Bloqueia até resolver' : 'Informativa',
                'observacao' => $dependencia->notes,
            ])
            ->values()
            ->all();
        $dados['bloqueios_lista'] = $item->blockers
            ->take(4)
            ->map(fn ($dependencia): array => [
                'titulo' => $dependencia->itemControle?->titulo ?? 'Item impactado',
                'status' => $dependencia->blocked_until_resolved ? 'Bloqueia até resolver' : 'Informativa',
            ])
            ->values()
            ->all();
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

        app(ItemControleOperationalService::class)->alterarStatus(
            $item,
            $status,
            Filament::auth()->user(),
            'kanban',
            'Status alterado pelo quadro Kanban.'
        );

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
