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

    public string $dateRange = 'all';
    public ?string $customStartDate = null;
    public ?string $customEndDate = null;
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
        if (! in_array($range, ['all', 'today', 'yesterday', 'last_7_days', 'last_30_days', 'custom'], true)) {
            return;
        }

        $this->dateRange = $range;

        if ($range === 'custom') {
            $this->customStartDate ??= now()->subDays(7)->toDateString();
            $this->customEndDate ??= now()->toDateString();
        }
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
        $this->dateRange = 'all';
        $this->customStartDate = null;
        $this->customEndDate = null;
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
        $allowed = ['all', 'risk', 'late', 'approval', 'correction', 'financial', 'no_owner', 'blocked', 'pendente', 'em_andamento'];

        if (! in_array($status, $allowed, true)) {
            return;
        }

        $this->statusFilter = $status;
    }


    public function applyKpiShortcut(string $status = 'all', ?string $dateRange = null): void
    {
        $this->applyStatusShortcut($status);

        if ($dateRange !== null) {
            $this->setDateRange($dateRange);
        }
    }

    protected function dashboardFilters(): array
    {
        return [
            'date_range' => $this->dateRange,
            'custom_start_date' => $this->customStartDate,
            'custom_end_date' => $this->customEndDate,
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
            'decision_summary' => $this->itemDecisionSummary($item, $value),
            'risk_signals' => $this->itemRiskSignals($item, $checklist, $timeline),
            'next_steps' => $this->itemNextSteps($item),
            'timeline' => $timeline,
            'checklist' => $checklist,
            'related_client_items' => $relatedClientItems,
            'operational_playbook' => $this->itemOperationalPlaybook($item, $checklist, $timeline),
            'decision_questions' => $this->itemDecisionQuestions($item, $checklist, $timeline),
            'communication_script' => $this->itemCommunicationScript($item),
            'success_criteria' => $this->itemSuccessCriteria($item, $checklist),
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
            'is_late' => (bool) ($item->data_vencimento && $item->data_vencimento->copy()->startOfDay()->lessThan(now()->startOfDay())),
            'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
        ])->values()->toArray();

        $criticalItem = $items->first(fn (ItemControle $item): bool => in_array((string) $item->prioridade, ['critica', 'urgente', 'alta'], true)) ?: $items->first();

        $lateCount = $items->filter(fn (ItemControle $item): bool => (bool) ($item->data_vencimento && $item->data_vencimento->copy()->startOfDay()->lessThan(now()->startOfDay())))->count();
        $criticalCount = $items->whereIn('prioridade', ['critica', 'urgente', 'alta'])->count();

        return [
            'responsavel' => $responsavel,
            'items' => $itemsPayload,
            'total' => $items->count(),
            'critical' => $criticalCount,
            'late' => $lateCount,
            'bottleneck_summary' => $this->workloadBottleneckSummary($items->count(), $criticalCount, $lateCount),
            'workload_signals' => $this->workloadSignals($itemsPayload, $criticalCount, $lateCount),
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


    protected function itemOperationalPlaybook(ItemControle $item, array $checklist, array $timeline): array
    {
        $status = (string) $item->status;
        $priority = (string) ($item->prioridade ?: 'normal');
        $company = $item->empresa?->razao_social ?: 'cliente';
        $responsavel = $item->responsavel?->nome ?: 'responsável ainda não definido';
        $openChecklist = collect($checklist)->where('concluido', false)->count();
        $lastMove = collect($timeline)->first();

        if ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()) {
            return [
                [
                    'label' => '1. Diagnóstico rápido',
                    'text' => "O item já está fora do prazo. Antes de abrir novas demandas, confirme o impedimento real: pendência do cliente, falta de documento, aprovação parada ou responsável sem capacidade.",
                ],
                [
                    'label' => '2. Ação de destravamento',
                    'text' => "Acione {$responsavel}, registre o motivo do atraso e resolva o bloqueio principal. Se não houver resposta imediata, delegue para alguém disponível e mantenha histórico.",
                ],
                [
                    'label' => '3. Proteção do cliente',
                    'text' => "Avise {$company} somente com informação objetiva: o que falta, quem está tratando e qual é o próximo retorno. Isso reduz cobrança, retrabalho e sensação de abandono.",
                ],
            ];
        }

        if (in_array($status, ['aguardando_aprovacao', 'em_aprovacao'], true)) {
            return [
                [
                    'label' => '1. Conferir antes de decidir',
                    'text' => 'Revise descrição, checklist, histórico e anexos antes de aprovar. A aprovação sem conferência vira retrabalho quando o cliente ou financeiro questiona depois.',
                ],
                [
                    'label' => '2. Decisão recomendada',
                    'text' => $openChecklist > 0 ? "Há {$openChecklist} etapa(s) pendente(s). Solicite correção com orientação objetiva em vez de aprovar no escuro." : 'Se o checklist e o histórico estiverem coerentes, aprove agora para não bloquear a próxima etapa.',
                ],
                [
                    'label' => '3. Registrar motivo',
                    'text' => 'Ao aprovar, reprovar ou pedir correção, deixe o motivo claro. Isso cria rastreabilidade e evita que a equipe repita a mesma dúvida.',
                ],
            ];
        }

        if (in_array($status, ['correcao_necessaria', 'reprovado'], true)) {
            return [
                [
                    'label' => '1. Localizar causa do retrabalho',
                    'text' => 'Identifique exatamente o que foi reprovado: dado incorreto, documento ausente, prazo incompatível, informação do cliente ou etapa interna não cumprida.',
                ],
                [
                    'label' => '2. Direcionar sem ambiguidade',
                    'text' => "Envie para {$responsavel} com uma instrução que possa ser executada sem nova reunião. Correção genérica tende a voltar errada.",
                ],
                [
                    'label' => '3. Fechar o ciclo',
                    'text' => 'Depois da correção, confira se o mesmo erro não aparece no checklist ou em outros itens recentes do mesmo cliente.',
                ],
            ];
        }

        if (! $item->responsavel_id) {
            return [
                [
                    'label' => '1. Definir dono',
                    'text' => 'Este item está sem responsável. A primeira decisão útil é escolher quem responde pela próxima ação e pelo prazo.',
                ],
                [
                    'label' => '2. Dar contexto mínimo',
                    'text' => "Ao delegar, inclua objetivo, prazo, cliente e impedimento conhecido. Delegar sem contexto só transfere o problema.",
                ],
                [
                    'label' => '3. Acompanhar retorno',
                    'text' => 'Depois de delegar, valide se houve movimentação. Se continuar parado, trate como gargalo operacional.',
                ],
            ];
        }

        if ($item->data_vencimento?->isToday()) {
            return [
                [
                    'label' => '1. Fechar o que vence hoje',
                    'text' => 'Confirme se falta documento, aprovação, pagamento, assinatura ou execução técnica. Hoje não é dia de apenas monitorar.',
                ],
                [
                    'label' => '2. Escolher saída segura',
                    'text' => 'Concluir se estiver pronto, solicitar correção se houver erro ou delegar se o responsável não conseguir finalizar dentro do dia.',
                ],
                [
                    'label' => '3. Evitar nova virada de prazo',
                    'text' => 'Registre a decisão tomada para que amanhã o item não apareça como atraso sem explicação.',
                ],
            ];
        }

        return [
            [
                'label' => '1. Confirmar prioridade real',
                'text' => in_array($priority, ['critica', 'urgente', 'alta'], true) ? 'A prioridade está elevada. Verifique se o risco é prazo, cliente, financeiro ou retrabalho.' : 'O item não parece crítico, mas entrou no radar. Confirme se existe algum bloqueio oculto.',
            ],
            [
                'label' => '2. Usar histórico como evidência',
                'text' => $lastMove ? "Última movimentação: {$lastMove['titulo']}. Use isso para decidir se acompanha, cobra ou redistribui." : 'Sem histórico recente. Abra o cadastro ou cobre atualização antes que a equipe perca contexto.',
            ],
            [
                'label' => '3. Sair com uma próxima ação',
                'text' => 'O modal deve terminar com decisão: aprovar, corrigir, delegar, abrir cadastro ou registrar acompanhamento. Não feche sem definir o próximo passo.',
            ],
        ];
    }

    protected function itemDecisionQuestions(ItemControle $item, array $checklist, array $timeline): array
    {
        $questions = [];

        if (! $item->responsavel_id) {
            $questions[] = 'Quem é o dono da próxima ação e até quando ele deve retornar?';
        }

        if ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()) {
            $questions[] = 'Qual foi o motivo real do atraso e ele já foi registrado?';
        }

        if (in_array((string) $item->status, ['aguardando_aprovacao', 'em_aprovacao'], true)) {
            $questions[] = 'Existe alguma etapa pendente que impede aprovação segura?';
        }

        if (in_array((string) $item->status, ['correcao_necessaria', 'reprovado'], true)) {
            $questions[] = 'A correção solicitada explica exatamente o que precisa mudar?';
        }

        if (collect($checklist)->where('concluido', false)->count() > 0) {
            $questions[] = 'As pendências do checklist são obrigatórias ou podem ser replanejadas?';
        }

        if (empty($timeline)) {
            $questions[] = 'Por que não há movimentação recente registrada?';
        }

        $questions[] = 'Qual ação reduz mais risco agora: concluir, corrigir, delegar ou cobrar o cliente?';

        return array_slice(array_values(array_unique($questions)), 0, 5);
    }

    protected function itemCommunicationScript(ItemControle $item): array
    {
        $company = $item->empresa?->razao_social ?: 'cliente';
        $title = $item->titulo ?: 'item operacional';
        $responsavel = $item->responsavel?->nome ?: 'responsável da equipe';
        $deadline = $item->data_vencimento?->format('d/m/Y') ?: 'sem prazo definido';

        if ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()) {
            return [
                'title' => 'Mensagem curta para reduzir cobrança',
                'text' => "Olá, {$company}. Identificamos que {$title} está fora do prazo previsto ({$deadline}) e já estamos tratando com {$responsavel}. O próximo passo é confirmar o bloqueio e registrar a regularização ainda no fluxo do item.",
            ];
        }

        if (in_array((string) $item->status, ['correcao_necessaria', 'reprovado'], true)) {
            return [
                'title' => 'Mensagem para solicitar correção sem retrabalho',
                'text' => "Olá, {$responsavel}. O item {$title} precisa de correção. Por favor, revise o ponto indicado, atualize o registro e deixe no histórico o que foi ajustado para podermos validar sem nova rodada de dúvidas.",
            ];
        }

        if (in_array((string) $item->status, ['aguardando_aprovacao', 'em_aprovacao'], true)) {
            return [
                'title' => 'Mensagem para destravar aprovação',
                'text' => "Olá, {$responsavel}. O item {$title} está aguardando aprovação. Confirme checklist, histórico e pendências. Se estiver correto, aprove; se faltar algo, solicite correção com o motivo objetivo.",
            ];
        }

        if (! $item->responsavel_id) {
            return [
                'title' => 'Mensagem para delegar com contexto',
                'text' => "Olá. Precisamos definir responsável para {$title}. Ao assumir, registre o próximo passo, prazo de retorno e qualquer bloqueio encontrado para evitar que o item fique invisível na operação.",
            ];
        }

        return [
            'title' => 'Mensagem para manter tração',
            'text' => "Olá, {$responsavel}. O item {$title} está no radar do Centro Operacional. Atualize o histórico com o próximo passo, impedimento atual ou previsão de conclusão para manter a equipe alinhada.",
        ];
    }

    protected function itemSuccessCriteria(ItemControle $item, array $checklist): array
    {
        $criteria = [];

        if (! $item->responsavel_id) {
            $criteria[] = 'Responsável definido e visível no item.';
        }

        if ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()) {
            $criteria[] = 'Motivo do atraso registrado no histórico.';
            $criteria[] = 'Nova ação ou regularização definida para impedir nova cobrança.';
        }

        if (in_array((string) $item->status, ['aguardando_aprovacao', 'em_aprovacao'], true)) {
            $criteria[] = 'Item aprovado, reprovado ou devolvido para correção com motivo claro.';
        }

        if (in_array((string) $item->status, ['correcao_necessaria', 'reprovado'], true)) {
            $criteria[] = 'Correção enviada para a pessoa certa com instrução objetiva.';
        }

        if (collect($checklist)->where('concluido', false)->count() > 0) {
            $criteria[] = 'Checklist pendente validado ou replanejado.';
        }

        $criteria[] = 'Próximo passo registrado para a equipe não perder contexto.';

        return array_slice(array_values(array_unique($criteria)), 0, 5);
    }

    protected function itemDecisionSummary(ItemControle $item, ?float $value): array
    {
        $deadline = $this->deadlineLabel($item);
        $status = (string) $item->status;
        $priority = (string) ($item->prioridade ?: 'normal');
        $company = $item->empresa?->razao_social ?: 'este cliente';

        $problem = 'Item operacional precisa de acompanhamento.';
        $impact = 'Pode gerar retrabalho ou atraso se ficar sem responsável claro.';
        $action = 'Conferir contexto e definir a próxima ação.';
        $tone = 'info';

        if ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()) {
            $problem = 'Prazo já vencido.';
            $impact = "Pode gerar atraso operacional, retrabalho e cobrança do cliente {$company}.";
            $action = 'Resolver antes de iniciar novas demandas ou redistribuir agora.';
            $tone = 'danger';
        } elseif ($item->data_vencimento?->isToday()) {
            $problem = 'Prazo vence hoje.';
            $impact = 'Se não for tratado hoje, entra na fila de atraso e aumenta o risco de retrabalho.';
            $action = 'Validar pendências e concluir ou delegar ainda hoje.';
            $tone = 'warning';
        } elseif (in_array($status, ['aguardando_aprovacao', 'em_aprovacao'], true)) {
            $problem = 'Decisão parada em aprovação.';
            $impact = 'A execução pode ficar bloqueada mesmo com a tarefa tecnicamente pronta.';
            $action = 'Aprovar, reprovar ou solicitar correção com base no checklist.';
            $tone = 'warning';
        } elseif (in_array($status, ['correcao_necessaria', 'reprovado'], true)) {
            $problem = 'Correção bloqueando o avanço.';
            $impact = 'Pode virar retrabalho recorrente se o motivo não for direcionado corretamente.';
            $action = 'Enviar para o responsável certo com orientação objetiva.';
            $tone = 'warning';
        } elseif (in_array($priority, ['critica', 'urgente', 'alta'], true)) {
            $problem = 'Prioridade alta na fila operacional.';
            $impact = 'Pode afetar prazo, cliente ou etapa crítica se perder tração.';
            $action = 'Garantir responsável, prazo e próximo passo definidos.';
            $tone = 'orange';
        }

        return [
            'tone' => $tone,
            'problem' => $problem,
            'impact' => $impact,
            'action' => $action,
            'deadline' => $deadline ?: 'Sem prazo calculado',
            'financial' => $value !== null ? 'Impacto financeiro informado: R$ ' . number_format($value, 2, ',', '.') : 'Sem valor financeiro informado; tratar pelo impacto operacional.',
        ];
    }

    protected function itemRiskSignals(ItemControle $item, array $checklist, array $timeline): array
    {
        $signals = [];

        if ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()) {
            $signals[] = ['tone' => 'danger', 'label' => 'Prazo vencido', 'text' => 'Este item já passou da data prevista e deve subir na fila de decisão.'];
        } elseif ($item->data_vencimento?->isToday()) {
            $signals[] = ['tone' => 'warning', 'label' => 'Vence hoje', 'text' => 'Precisa de fechamento ou encaminhamento ainda hoje.'];
        } elseif (! $item->data_vencimento) {
            $signals[] = ['tone' => 'warning', 'label' => 'Sem prazo', 'text' => 'Sem data de vencimento, o item pode sumir da prioridade diária.'];
        }

        if (! $item->responsavel_id) {
            $signals[] = ['tone' => 'danger', 'label' => 'Sem responsável', 'text' => 'Ninguém está claramente dono da próxima ação.'];
        }

        if (in_array((string) $item->status, ['correcao_necessaria', 'reprovado'], true)) {
            $signals[] = ['tone' => 'warning', 'label' => 'Retrabalho', 'text' => 'Existe correção ou reprovação impedindo o avanço normal.'];
        }

        if (in_array((string) $item->prioridade, ['critica', 'urgente', 'alta'], true)) {
            $signals[] = ['tone' => 'orange', 'label' => 'Prioridade elevada', 'text' => 'A demanda já foi marcada como relevante para a operação.'];
        }

        $openChecklist = collect($checklist)->where('concluido', false)->count();
        if ($openChecklist > 0) {
            $signals[] = ['tone' => 'info', 'label' => 'Checklist pendente', 'text' => $openChecklist . ' etapa(s) ainda precisam de validação.'];
        }

        if (empty($timeline)) {
            $signals[] = ['tone' => 'warning', 'label' => 'Sem histórico recente', 'text' => 'Não há movimentação registrada para explicar o andamento.'];
        }

        return array_slice($signals, 0, 5);
    }

    protected function itemNextSteps(ItemControle $item): array
    {
        $steps = [];
        $status = (string) $item->status;

        if (! $item->responsavel_id) {
            $steps[] = 'Definir responsável antes de qualquer outra ação.';
        }

        if ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()) {
            $steps[] = 'Verificar imediatamente o motivo do atraso.';
            $steps[] = 'Regularizar, concluir ou redistribuir para alguém disponível.';
        } elseif ($item->data_vencimento?->isToday()) {
            $steps[] = 'Conferir o que falta para fechar ainda hoje.';
        }

        if (in_array($status, ['aguardando_aprovacao', 'em_aprovacao'], true)) {
            $steps[] = 'Aprovar se estiver correto ou solicitar correção com orientação clara.';
        }

        if (in_array($status, ['correcao_necessaria', 'reprovado'], true)) {
            $steps[] = 'Identificar o motivo da correção e devolver para quem consegue resolver.';
        }

        if (empty($steps)) {
            $steps[] = 'Validar checklist, histórico e responsável.';
            $steps[] = 'Manter acompanhamento ou abrir cadastro se precisar de detalhe técnico.';
        }

        return array_slice($steps, 0, 4);
    }

    protected function workloadBottleneckSummary(int $total, int $critical, int $late): array
    {
        if ($late > 0) {
            return [
                'tone' => 'danger',
                'title' => 'Gargalo com atraso real',
                'text' => "Há {$late} item(ns) atrasado(s). A prioridade é remover atraso antes de puxar novas tarefas.",
                'action' => 'Redistribuir item atrasado ou crítico',
            ];
        }

        if ($critical > 0) {
            return [
                'tone' => 'warning',
                'title' => 'Carga com itens críticos',
                'text' => "Há {$critical} item(ns) crítico(s) na fila. O risco não é quantidade, é impacto se travar.",
                'action' => 'Proteger os itens críticos',
            ];
        }

        if ($total >= 10) {
            return [
                'tone' => 'orange',
                'title' => 'Carga alta em acompanhamento',
                'text' => 'A fila está grande. Vale revisar se há tarefas simples que podem ser delegadas.',
                'action' => 'Avaliar redistribuição preventiva',
            ];
        }

        return [
            'tone' => 'info',
            'title' => 'Carga sob controle',
            'text' => 'Não há sinal forte de gargalo neste responsável no momento.',
            'action' => 'Monitorar',
        ];
    }

    protected function workloadSignals(array $items, int $critical, int $late): array
    {
        $signals = [];

        $signals[] = ['label' => 'Atrasados', 'value' => number_format($late, 0, ',', '.'), 'text' => $late > 0 ? 'Exigem decisão antes de novas demandas.' : 'Sem atraso nos itens listados.'];
        $signals[] = ['label' => 'Críticos', 'value' => number_format($critical, 0, ',', '.'), 'text' => $critical > 0 ? 'Podem afetar prazo, cliente ou retrabalho.' : 'Sem item crítico na amostra atual.'];

        $today = collect($items)->filter(fn (array $item): bool => str_contains((string) ($item['dias_prazo'] ?? ''), 'hoje'))->count();
        $signals[] = ['label' => 'Vencem hoje', 'value' => number_format($today, 0, ',', '.'), 'text' => $today > 0 ? 'Precisam de fechamento no dia.' : 'Nenhum vencimento hoje na lista.'];

        return $signals;
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
        $responsaveis = $this->allowedResponsaveisForDelegation($item)
            ->where('id', '<>', $item->responsavel_id)
            ->get(['id', 'nome']);

        if ($responsaveis->isEmpty()) {
            return [
                'title' => 'Sem alternativa automática segura',
                'text' => 'Não encontrei outro responsável disponível dentro do escopo permitido. Use a lista manual se existir alguém habilitado.',
                'target_id' => null,
            ];
        }

        $responsavelIds = $responsaveis->pluck('id')->all();
        $openCounts = ItemControle::query()
            ->visibleForUser(Filament::auth()->user())
            ->whereIn('responsavel_id', $responsavelIds)
            ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
            ->selectRaw('responsavel_id, COUNT(*) as total_abertos')
            ->groupBy('responsavel_id')
            ->pluck('total_abertos', 'responsavel_id');

        $candidate = $responsaveis
            ->map(fn (Responsavel $responsavel): array => [
                'id' => $responsavel->id,
                'nome' => $responsavel->nome ?: 'Responsável',
                'open_count' => (int) ($openCounts[$responsavel->id] ?? 0),
            ])
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

        $dueDate = $item->data_vencimento->copy()->startOfDay();
        $today = now()->startOfDay();

        if ($dueDate->equalTo($today)) {
            return 'Vence hoje';
        }

        if ($dueDate->lessThan($today)) {
            return 'Vencido há ' . $dueDate->diffInDays($today) . ' dia(s)';
        }

        return 'Faltam ' . $today->diffInDays($dueDate) . ' dia(s)';
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
