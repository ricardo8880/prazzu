<?php

namespace App\Filament\Pages;

use App\Models\ItemControle;
use App\Models\Responsavel;
use App\Services\CentroOperacionalService;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Support\CachedSchema;
use App\Support\CentroOperacionalAccess;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CentroOperacional extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-command-line';
    protected static string | UnitEnum | null $navigationGroup = 'Trabalho';
    protected static ?string $navigationLabel = 'Centro Operacional';
    protected static ?string $title = 'Centro Operacional';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.centro-operacional';

    public string $dateRange = 'today';
    public string $deadlinePeriod = 'today';
    public string $statusFilter = 'all';
    public string $departmentFilter = 'all';
    public string $globalSearch = '';
    public ?int $delegateItemId = null;
    public ?int $delegateResponsavelId = null;
    public bool $delegateModalOpen = false;
    public bool $detailModalOpen = false;
    public ?int $detailItemId = null;
    public string $detailModalSource = 'resolver';
    public bool $workloadModalOpen = false;
    public ?int $workloadResponsavelId = null;
    public ?int $redistributionItemId = null;
    public ?int $redistributionResponsavelId = null;

    public function getHeading(): string
    {
        return '';
    }

    protected function getViewData(): array
    {
        return [
            'data' => app(CentroOperacionalService::class)->dashboard(Filament::auth()->user(), $this->dashboardFilters()),
        ];
    }

    public function setDateRange(string $range): void
    {
        if (! in_array($range, ['today', 'seven_days', 'fifteen_days', 'month'], true)) {
            return;
        }

        $this->dateRange = $range;
    }

    public function setDeadlinePeriod(string $period): void
    {
        if (! in_array($period, ['today', 'seven_days', 'fifteen_days', 'thirty_days'], true)) {
            return;
        }

        $this->deadlinePeriod = $period;
    }

    public function resetOperationalFilters(): void
    {
        $this->dateRange = 'today';
        $this->deadlinePeriod = 'today';
        $this->statusFilter = 'all';
        $this->departmentFilter = 'all';
    }

    public function refreshDashboard(): void
    {
        $this->notifySuccess('Centro Operacional atualizado.');
    }


    public function clearGlobalSearch(): void
    {
        $this->globalSearch = '';
    }

    public function updatedGlobalSearch(): void
    {
        $this->globalSearch = trim(str($this->globalSearch)->limit(80, '')->toString());
    }


    public function applyStatusShortcut(string $status): void
    {
        $allowed = ['all', 'late', 'approval', 'correction', 'financial', 'no_owner', 'blocked', 'pendente', 'em_andamento'];

        if (! in_array($status, $allowed, true)) {
            return;
        }

        $this->statusFilter = $status;
    }

    protected function dashboardFilters(): array
    {
        return [
            'date_range' => $this->dateRange,
            'deadline_period' => $this->deadlinePeriod,
            'status' => $this->statusFilter,
            'department' => $this->departmentFilter,
            'global_search' => $this->globalSearch,
        ];
    }

    public function aprovar(int $id): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_APPROVE);

        if (! $item) {
            return;
        }

        if (! in_array((string) $item->status, ['aguardando_aprovacao', 'em_aprovacao', 'reprovado'], true)) {
            $this->notifyError('Este item não está em um status que permita aprovação pelo Centro Operacional.');
            return;
        }

        if (! $item->aprovar(Filament::auth()->user(), 'Aprovado pelo Centro Operacional.')) {
            $this->notifyError('Não foi possível aprovar este item.');
            return;
        }

        $this->notifySuccess('Item aprovado com sucesso.');
    }

    public function reprovar(int $id): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_APPROVE);

        if (! $item) {
            return;
        }

        if (! in_array((string) $item->status, ['aguardando_aprovacao', 'em_aprovacao', 'aprovado'], true)) {
            $this->notifyError('Este item não está em um status que permita reprovação pelo Centro Operacional.');
            return;
        }

        if (! $item->reprovar(Filament::auth()->user(), 'Reprovado pelo Centro Operacional.')) {
            $this->notifyError('Não foi possível reprovar este item.');
            return;
        }

        $this->notifySuccess('Item reprovado com sucesso.');
    }

    public function abrirPendenciasFinanceiras(): void
    {
        $this->statusFilter = 'financial';
    }

    public function abrirAprovacoes(): void
    {
        $this->statusFilter = 'approval';
    }

    public function abrirTarefasResponsavel(?int $responsavelId = null): void
    {
        if ($responsavelId) {
            $this->openWorkloadModal($responsavelId);
            return;
        }

        $this->statusFilter = 'all';

        $this->redirect(ItemControleResource::getUrl('index'));
    }

    public function openItemDetailModal(int $id, string $source = 'resolver'): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_VIEW);

        if (! $item) {
            return;
        }

        $this->detailItemId = $item->id;
        $this->detailModalSource = in_array($source, ['resolver', 'cliente'], true) ? $source : 'resolver';
        $this->detailModalOpen = true;
    }

    public function closeItemDetailModal(): void
    {
        $this->detailModalOpen = false;
        $this->detailItemId = null;
        $this->detailModalSource = 'resolver';
    }

    public function selectedItemDetail(): ?array
    {
        if (! $this->detailItemId) {
            return null;
        }

        $item = ItemControle::query()
            ->visibleForUser(Filament::auth()->user())
            ->with(['empresa', 'responsavel', 'categoria'])
            ->whereKey($this->detailItemId)
            ->first();

        if (! $item) {
            return null;
        }

        $description = filled($item->descricao)
            ? (string) $item->descricao
            : 'Sem descrição cadastrada.';

        $value = null;
        if (CachedSchema::hasColumn('item_controles', 'valor_tarefa') && filled($item->valor_tarefa)) {
            $value = (float) $item->valor_tarefa;
        } elseif (filled($item->contrato_valor)) {
            $value = (float) $item->contrato_valor;
        }

        $timeline = CachedSchema::hasTable('item_controle_timeline')
            ? $item->timelines()
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn ($entry): array => [
                    'tipo' => method_exists($entry, 'getTipoExibicao') ? $entry->getTipoExibicao() : str((string) $entry->tipo)->replace('_', ' ')->title()->toString(),
                    'titulo' => $entry->titulo ?: 'Atualização operacional',
                    'descricao' => $entry->descricao ?: 'Sem detalhe adicional.',
                    'data' => $entry->created_at?->format('d/m/Y H:i') ?: '-',
                ])
                ->values()
                ->toArray()
            : [];

        $checklist = CachedSchema::hasTable('item_controle_checklists')
            ? $item->checklists()
                ->orderBy('ordem')
                ->orderBy('id')
                ->limit(6)
                ->get()
                ->map(fn ($check): array => [
                    'titulo' => $check->titulo ?: 'Etapa operacional',
                    'concluido' => (bool) $check->concluido,
                ])
                ->values()
                ->toArray()
            : [];

        $relatedClientItems = $item->empresa_id
            ? ItemControle::query()
                ->visibleForUser(Filament::auth()->user())
                ->with(['categoria:id,nome', 'responsavel:id,nome'])
                ->where('empresa_id', $item->empresa_id)
                ->where('id', '<>', $item->id)
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn (ItemControle $related): array => [
                    'titulo' => $related->titulo ?: 'Item operacional',
                    'status' => str((string) $related->status)->replace('_', ' ')->title()->toString(),
                    'responsavel' => $related->responsavel?->nome ?: 'Sem responsável',
                    'vencimento' => $related->data_vencimento?->format('d/m/Y') ?: 'Sem prazo',
                    'url' => ItemControleResource::getUrl('edit', ['record' => $related]),
                ])
                ->values()
                ->toArray()
            : [];

        return [
            'id' => $item->id,
            'title' => $item->titulo ?: 'Item operacional',
            'empresa' => $item->empresa?->razao_social ?: 'Sem empresa vinculada',
            'responsavel' => $item->responsavel?->nome ?: 'Sem responsável',
            'categoria' => $item->categoria?->nome ?: ($item->getTipoOuCategoria() ?: 'Operacional'),
            'status' => str((string) $item->status)->replace('_', ' ')->title()->toString(),
            'status_key' => (string) $item->status,
            'prioridade' => str((string) ($item->prioridade ?: 'normal'))->replace('_', ' ')->title()->toString(),
            'vencimento' => $item->data_vencimento?->format('d/m/Y') ?: 'Sem prazo',
            'dias_prazo' => $this->deadlineLabel($item),
            'conclusao' => $item->data_conclusao?->format('d/m/Y') ?: 'Não concluído',
            'descricao' => $description,
            'valor' => $value !== null ? 'R$ ' . number_format($value, 2, ',', '.') : 'Sem valor informado',
            'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
            'actions' => CentroOperacionalAccess::actionPermissions(Filament::auth()->user(), $item),
            'is_closed' => in_array((string) $item->status, ['concluido', 'cancelado'], true),
            'suggestion' => $this->operationalSuggestion($item),
            'timeline' => $timeline,
            'checklist' => $checklist,
            'related_client_items' => $relatedClientItems,
        ];
    }

    public function openWorkloadModal(int $responsavelId): void
    {
        $responsavel = Responsavel::query()
            ->whereKey($responsavelId)
            ->first();

        if (! $responsavel) {
            $this->notifyError('Responsável não encontrado.');
            return;
        }

        $this->workloadResponsavelId = $responsavel->id;
        $this->redistributionItemId = null;
        $this->redistributionResponsavelId = null;
        $this->workloadModalOpen = true;
    }

    public function closeWorkloadModal(): void
    {
        $this->workloadModalOpen = false;
        $this->workloadResponsavelId = null;
        $this->redistributionItemId = null;
        $this->redistributionResponsavelId = null;
    }

    public function selectedWorkloadDetail(): array
    {
        if (! $this->workloadResponsavelId) {
            return ['responsavel' => null, 'items' => [], 'total' => 0, 'critical' => 0, 'late' => 0];
        }

        $responsavel = Responsavel::query()->whereKey($this->workloadResponsavelId)->first();

        if (! $responsavel) {
            return ['responsavel' => null, 'items' => [], 'total' => 0, 'critical' => 0, 'late' => 0];
        }

        $items = ItemControle::query()
            ->visibleForUser(Filament::auth()->user())
            ->with(['empresa:id,razao_social', 'categoria:id,nome'])
            ->where('responsavel_id', $responsavel->id)
            ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
            ->orderByRaw("CASE WHEN prioridade IN ('critica', 'urgente') THEN 1 WHEN prioridade = 'alta' THEN 2 ELSE 3 END")
            ->orderBy('data_vencimento')
            ->limit(12)
            ->get();

        $itemsPayload = $items->map(fn (ItemControle $item): array => [
            'id' => $item->id,
            'title' => $item->titulo ?: 'Item operacional',
            'empresa' => $item->empresa?->razao_social ?: 'Sem empresa',
            'categoria' => $item->categoria?->nome ?: ($item->getTipoOuCategoria() ?: 'Operacional'),
            'status' => str((string) $item->status)->replace('_', ' ')->title()->toString(),
            'prioridade' => str((string) ($item->prioridade ?: 'normal'))->replace('_', ' ')->title()->toString(),
            'vencimento' => $item->data_vencimento?->format('d/m/Y') ?: 'Sem prazo',
            'dias_prazo' => $this->deadlineLabel($item),
            'is_late' => (bool) ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()),
            'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
        ])->values()->toArray();

        $criticalItem = $items->first(fn (ItemControle $item): bool => in_array((string) $item->prioridade, ['critica', 'urgente', 'alta'], true)) ?: $items->first();

        return [
            'responsavel' => $responsavel,
            'items' => $itemsPayload,
            'total' => $items->count(),
            'critical' => $items->whereIn('prioridade', ['critica', 'urgente', 'alta'])->count(),
            'late' => $items->filter(fn (ItemControle $item): bool => (bool) ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()))->count(),
            'recommendation' => $criticalItem ? $this->workloadRedistributionSuggestion($criticalItem) : null,
        ];
    }

    public function redistribuirItemSelecionado(): void
    {
        if (! $this->redistributionItemId || ! $this->redistributionResponsavelId) {
            $this->notifyError('Selecione a tarefa e o novo responsável.');
            return;
        }

        $item = $this->findAllowedItem($this->redistributionItemId, CentroOperacionalAccess::ACTION_DELEGATE);

        if (! $item) {
            return;
        }

        $novoResponsavel = $this->allowedResponsaveisForDelegation($item)
            ->whereKey($this->redistributionResponsavelId)
            ->first();

        if (! $novoResponsavel) {
            $this->notifyError('Responsável inválido para redistribuição.');
            return;
        }

        if ((int) $item->responsavel_id === (int) $novoResponsavel->id) {
            $this->notifyError('Este item já está com esse responsável.');
            return;
        }

        $responsavelAnterior = $item->responsavel?->nome ?: 'Sem responsável';
        $responsavelNovo = $novoResponsavel->nome ?: 'Novo responsável';

        $item->update(['responsavel_id' => $novoResponsavel->id]);
        $item->registrarTimeline(
            'redistribuicao',
            'Item redistribuído pelo Centro Operacional',
            "Responsável alterado de {$responsavelAnterior} para {$responsavelNovo} pelo painel de Workload.",
            ['responsavel_anterior_id' => $this->workloadResponsavelId, 'responsavel_novo_id' => $novoResponsavel->id],
            Filament::auth()->user()
        );

        $this->redistributionItemId = null;
        $this->redistributionResponsavelId = null;
        $this->notifySuccess('Item redistribuído com sucesso.');
    }


    public function preencherSugestaoRedistribuicao(int $itemId, int $responsavelId): void
    {
        $item = $this->findAllowedItem($itemId, CentroOperacionalAccess::ACTION_DELEGATE);

        if (! $item) {
            return;
        }

        $responsavel = $this->allowedResponsaveisForDelegation($item)
            ->whereKey($responsavelId)
            ->first();

        if (! $responsavel) {
            $this->notifyError('Sugestão indisponível para o seu escopo de acesso.');
            return;
        }

        $this->redistributionItemId = $item->id;
        $this->redistributionResponsavelId = $responsavel->id;
        $this->notifySuccess('Sugestão aplicada. Confirme a redistribuição para concluir.');
    }

    public function enviarParaCorrecao(int $id): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_CORRECT);

        if (! $item) {
            return;
        }

        if (in_array((string) $item->status, ['concluido', 'cancelado'], true)) {
            $this->notifyError('Itens concluídos ou cancelados não podem ser enviados para correção pelo Centro Operacional.');
            return;
        }

        $payload = ['status' => 'correcao_necessaria'];
        if (CachedSchema::hasColumn('item_controles', 'status_operacional_at')) {
            $payload['status_operacional_at'] = now();
        }

        $item->update($payload);
        $item->registrarTimeline('correcao', 'Correção solicitada', 'Item enviado para correção pelo Centro Operacional.', null, Filament::auth()->user());
        $this->notifySuccess('Item enviado para correção.');
    }


    public function openDelegateModal(int $id): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_DELEGATE);

        if (! $item) {
            return;
        }

        if (in_array((string) $item->status, ['concluido', 'cancelado'], true)) {
            $this->notifyError('Itens concluídos ou cancelados não podem ser delegados pelo Centro Operacional.');
            return;
        }

        $this->delegateItemId = $item->id;
        $this->delegateResponsavelId = $item->responsavel_id ? (int) $item->responsavel_id : null;
        $this->delegateModalOpen = true;
    }

    public function cancelDelegateModal(): void
    {
        $this->delegateModalOpen = false;
        $this->delegateItemId = null;
        $this->delegateResponsavelId = null;
    }

    public function delegar(): void
    {
        if (! $this->delegateItemId) {
            $this->notifyError('Nenhum item selecionado para delegação.');
            return;
        }

        $item = $this->findAllowedItem($this->delegateItemId, CentroOperacionalAccess::ACTION_DELEGATE);

        if (! $item) {
            $this->cancelDelegateModal();
            return;
        }

        if (! $this->delegateResponsavelId) {
            $this->notifyError('Selecione o novo responsável.');
            return;
        }

        $novoResponsavel = $this->allowedResponsaveisForDelegation($item)
            ->whereKey($this->delegateResponsavelId)
            ->first();

        if (! $novoResponsavel) {
            $this->notifyError('Responsável inválido para este item ou fora do seu escopo de delegação.');
            return;
        }

        $responsavelAnteriorId = $item->responsavel_id ? (int) $item->responsavel_id : null;
        $responsavelAnterior = $item->responsavel?->nome ?: 'Sem responsável';
        $responsavelNovo = $novoResponsavel->nome ?: 'Novo responsável';

        if ((int) $item->responsavel_id === (int) $novoResponsavel->id) {
            $this->notifyError('Este responsável já está vinculado ao item.');
            return;
        }

        $item->update([
            'responsavel_id' => $novoResponsavel->id,
        ]);

        $item->registrarTimeline(
            'delegacao',
            'Item delegado pelo Centro Operacional',
            "Responsável alterado de {$responsavelAnterior} para {$responsavelNovo}.",
            [
                'responsavel_anterior_id' => $responsavelAnteriorId,
                'responsavel_novo_id' => $novoResponsavel->id,
            ],
            Filament::auth()->user()
        );

        $this->cancelDelegateModal();
        $this->notifySuccess('Item delegado com sucesso.');
    }

    public function delegateResponsavelOptions(): array
    {
        $targetItemId = $this->delegateItemId ?: $this->redistributionItemId;

        if (! $targetItemId) {
            return [];
        }

        $item = ItemControle::query()
            ->visibleForUser(Filament::auth()->user())
            ->whereKey($targetItemId)
            ->first();

        if (! $item) {
            return [];
        }

        return $this->allowedResponsaveisForDelegation($item)
            ->orderBy('nome')
            ->limit(80)
            ->pluck('nome', 'id')
            ->toArray();
    }

    protected function allowedResponsaveisForDelegation(ItemControle $item)
    {
        $user = Filament::auth()->user();

        $query = Responsavel::query()
            ->select(['id', 'nome', 'empresa_id', 'user_id', 'gestor_user_id'])
            ->whereNotNull('user_id');

        if ($user?->isSuperAdmin()) {
            return $query->when($item->empresa_id, fn ($query) => $query->where('empresa_id', $item->empresa_id));
        }

        if (! $user?->hasEmpresaVinculada()) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('empresa_id', $user->empresa_id);

        if ($user->isAdminEmpresa()) {
            return $query;
        }

        if ($user->isGestor()) {
            return $query->where(function ($query) use ($user): void {
                $query->where('gestor_user_id', $user->id)
                    ->orWhere('user_id', $user->id);
            });
        }

        return $query->where('user_id', $user->id);
    }

    public function marcarFaturado(int $id): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_FINANCIAL);

        if (! $item) {
            return;
        }

        if (! in_array((string) $item->status, ['concluido', 'aprovado', 'assinado'], true)) {
            $this->notifyError('Somente itens concluídos, aprovados ou assinados podem ser faturados pelo Centro Operacional.');
            return;
        }

        $payload = [];
        if (CachedSchema::hasColumn('item_controles', 'faturado_em')) {
            $payload['faturado_em'] = now();
        } else {
            $payload['contrato_status'] = 'faturado';
        }

        $item->update($payload);
        $item->registrarTimeline('financeiro', 'Item marcado como faturado', 'Cobrança atualizada pelo Centro Operacional.', null, Filament::auth()->user());
        $this->notifySuccess('Item marcado como faturado.');
    }

    public function marcarPago(int $id): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_FINANCIAL);

        if (! $item) {
            return;
        }

        if (! in_array((string) $item->status, ['concluido', 'aprovado', 'assinado'], true)) {
            $this->notifyError('Somente itens concluídos, aprovados ou assinados podem ser marcados como pagos pelo Centro Operacional.');
            return;
        }

        $payload = ['contrato_status' => 'pago'];
        if (CachedSchema::hasColumn('item_controles', 'pago_em')) {
            $payload['pago_em'] = now();
        }
        if (CachedSchema::hasColumn('item_controles', 'faturado_em') && blank($item->faturado_em)) {
            $payload['faturado_em'] = now();
        }

        $item->update($payload);
        $item->registrarTimeline('financeiro', 'Item marcado como pago', 'Pagamento atualizado pelo Centro Operacional.', null, Filament::auth()->user());
        $this->notifySuccess('Item marcado como pago.');
    }

    protected function operationalSuggestion(ItemControle $item): array
    {
        $status = (string) $item->status;
        $priority = (string) ($item->prioridade ?: 'normal');
        $actions = CentroOperacionalAccess::actionPermissions(Filament::auth()->user(), $item);

        if ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()) {
            return [
                'tone' => 'danger',
                'title' => 'Resolver antes de qualquer item novo',
                'text' => 'Este item já passou do prazo. Abra, valide o impedimento e delegue imediatamente se o responsável atual estiver sobrecarregado.',
                'primary_action' => ($actions['delegate'] ?? false) ? 'Delegar para responsável disponível' : 'Abrir cadastro e regularizar',
            ];
        }

        if (in_array($status, ['aguardando_aprovacao', 'em_aprovacao'], true)) {
            return [
                'tone' => 'warning',
                'title' => 'Decisão pendente de aprovação',
                'text' => 'Revise o contexto, aprove se estiver correto ou solicite correção sem sair do popup.',
                'primary_action' => ($actions['approve'] ?? false) ? 'Aprovar ou solicitar correção' : 'Abrir cadastro para acompanhar',
            ];
        }

        if (in_array($status, ['correcao_necessaria', 'reprovado'], true)) {
            return [
                'tone' => 'warning',
                'title' => 'Correção bloqueando avanço',
                'text' => 'Verifique o motivo da correção e direcione para o responsável certo para evitar retrabalho.',
                'primary_action' => ($actions['delegate'] ?? false) ? 'Delegar correção' : 'Abrir cadastro',
            ];
        }

        if ($item->data_vencimento?->isToday()) {
            return [
                'tone' => 'orange',
                'title' => 'Vence hoje',
                'text' => 'Esse item deve ser tratado ainda hoje. Confira responsável, checklist e últimas movimentações antes de decidir.',
                'primary_action' => 'Resolver hoje',
            ];
        }

        if (in_array($priority, ['critica', 'urgente', 'alta'], true)) {
            return [
                'tone' => 'orange',
                'title' => 'Prioridade alta',
                'text' => 'Item importante para a operação. Antecipe a decisão ou delegue se houver fila no responsável atual.',
                'primary_action' => ($actions['delegate'] ?? false) ? 'Avaliar delegação' : 'Acompanhar',
            ];
        }

        return [
            'tone' => 'info',
            'title' => 'Acompanhar sem urgência crítica',
            'text' => 'Item dentro do fluxo esperado. Use o histórico e o checklist para decidir se precisa de ação agora.',
            'primary_action' => 'Acompanhar andamento',
        ];
    }

    protected function workloadRedistributionSuggestion(ItemControle $item): array
    {
        $candidate = $this->allowedResponsaveisForDelegation($item)
            ->where('id', '<>', $item->responsavel_id)
            ->get()
            ->map(function (Responsavel $responsavel): array {
                $openCount = ItemControle::query()
                    ->visibleForUser(Filament::auth()->user())
                    ->where('responsavel_id', $responsavel->id)
                    ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
                    ->count();

                return [
                    'id' => $responsavel->id,
                    'nome' => $responsavel->nome ?: 'Responsável',
                    'open_count' => $openCount,
                ];
            })
            ->sortBy('open_count')
            ->first();

        if (! $candidate) {
            return [
                'title' => 'Sem alternativa automática segura',
                'text' => 'Não encontrei outro responsável disponível dentro do escopo permitido. Use a lista manual se existir alguém habilitado.',
                'target_id' => null,
            ];
        }

        return [
            'title' => 'Sugestão de redistribuição',
            'text' => "Mover uma tarefa prioritária para {$candidate['nome']}, que possui {$candidate['open_count']} item(ns) aberto(s).",
            'target_id' => $candidate['id'],
        ];
    }

    protected function deadlineLabel(ItemControle $item): string
    {
        if (! $item->data_vencimento) {
            return 'Sem prazo definido';
        }

        if ($item->data_vencimento->isToday()) {
            return 'Vence hoje';
        }

        if ($item->data_vencimento->isPast()) {
            return 'Vencido há ' . $item->data_vencimento->diffInDays(now()) . ' dia(s)';
        }

        return 'Faltam ' . now()->startOfDay()->diffInDays($item->data_vencimento->copy()->startOfDay()) . ' dia(s)';
    }

    protected function findAllowedItem(int $id, string $action = CentroOperacionalAccess::ACTION_VIEW): ?ItemControle
    {
        $user = Filament::auth()->user();

        $item = ItemControle::query()
            ->visibleForUser($user)
            ->whereKey($id)
            ->first();

        if (! $item) {
            $this->notifyError('Item não encontrado ou fora do seu escopo de acesso.');
            return null;
        }

        if (! CentroOperacionalAccess::can($user, $item, $action)) {
            $this->notifyError('Você não tem permissão para ' . CentroOperacionalAccess::actionLabel($action) . ' este item pelo Centro Operacional.');
            return null;
        }

        return $item;
    }

    protected function notifySuccess(string $message): void
    {
        Notification::make()->title($message)->success()->send();
    }

    protected function notifyError(string $message): void
    {
        Notification::make()->title($message)->danger()->send();
    }
}
