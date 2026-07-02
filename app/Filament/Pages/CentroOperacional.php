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
    protected static string | UnitEnum | null $navigationGroup = 'Operação';
    protected static ?string $navigationLabel = 'Mesa Operacional';
    protected static ?string $title = 'Mesa Operacional';
    protected static ?int $navigationSort = 10;
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
    public bool $detailPersonalizeOpen = false;
    public string $detailDraftMessage = '';
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


    public function criarTarefaOperacional(): void
    {
        $this->redirect(ItemControleResource::getUrl('create'));
    }

    public function abrirFilaOperacional(): void
    {
        $this->redirect(ItemControleResource::getUrl('index'));
    }

    public function abrirGestaoOperacional(string $aba = 'workload'): void
    {
        $this->notifySuccess('Gestão da Operação foi removida. Use a Mesa Operacional para acompanhar workload, aprovações e financeiro.');
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
        $this->detailDraftMessage = $this->itemReadyMessage($item);
        $this->detailPersonalizeOpen = false;
        $this->detailModalOpen = true;
    }

    public function closeItemDetailModal(): void
    {
        $this->detailModalOpen = false;
        $this->detailItemId = null;
        $this->detailModalSource = 'resolver';
        $this->detailPersonalizeOpen = false;
        $this->detailDraftMessage = '';
    }

    public function toggleDetailPersonalize(): void
    {
        $this->detailPersonalizeOpen = ! $this->detailPersonalizeOpen;
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
            'referencia' => $item->data_vencimento?->format('m/Y') ?: 'Sem referência',
            'estimated_time' => filled($item->estimated_minutes) ? ((int) $item->estimated_minutes . ' min') : 'Não calculado',
            'whatsapp_url' => $this->itemWhatsappUrl($item),
            'portal_cliente_url' => $this->itemPortalClienteUrl($item),
            'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
            'actions' => CentroOperacionalAccess::actionPermissions(Filament::auth()->user(), $item),
            'is_closed' => in_array((string) $item->status, ['concluido', 'cancelado'], true),
            'suggestion' => $this->operationalSuggestion($item),
            'decision_summary' => $this->itemDecisionSummary($item, $value),
            'risk_signals' => $this->itemRiskSignals($item, $checklist, $timeline),
            'next_steps' => $this->itemNextSteps($item),
            'executive_summary' => $this->itemExecutiveSummary($item, $value),
            'why_here' => $this->itemWhyHere($item, $checklist, $timeline),
            'operational_impact' => $this->itemOperationalImpact($item, $value, $relatedClientItems),
            'stalled_info' => $this->itemStalledInfo($item, $timeline),
            'ready_message' => $this->itemReadyMessage($item),
            'blockers' => $this->itemBlockers($item, $checklist, $timeline),
            'done_definition' => $this->itemDoneDefinition($item),
            'urgency_score' => $this->itemUrgencyScore($item, $checklist, $timeline, $relatedClientItems),
            'critical_client' => $this->itemCriticalClientInfo($item, $relatedClientItems),
            'client_risk_summary' => $this->itemClientRiskSummary($item, $checklist, $timeline, $relatedClientItems),
            'client_relationship' => $this->itemClientRelationship($item),
            'timeline' => $timeline,
            'checklist' => $checklist,
            'related_client_items' => $relatedClientItems,
        ];
    }

    public function registrarContatoCliente(int $id): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_VIEW);

        if (! $item) {
            return;
        }

        $message = trim($this->detailDraftMessage) !== '' ? trim($this->detailDraftMessage) : $this->itemReadyMessage($item);

        $item->registrarTimeline(
            'contato_cliente',
            'Contato com cliente iniciado pelo Centro Operacional',
            $message,
            ['canal' => $this->empresaTelefone($item) ? 'whatsapp' : 'mensagem_copiada'],
            Filament::auth()->user()
        );

        $this->notifySuccess($this->empresaTelefone($item) ? 'Contato registrado. O WhatsApp foi aberto com a mensagem pronta.' : 'Contato registrado. Copie a mensagem para enviar ao cliente.');
    }


    public function registrarContatoPortalCliente(int $id): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_VIEW);

        if (! $item) {
            return;
        }

        $message = trim($this->detailDraftMessage) !== '' ? trim($this->detailDraftMessage) : $this->itemReadyMessage($item);

        $item->registrarTimeline(
            'contato_cliente',
            'Contato pelo Portal do Cliente iniciado pelo Centro Operacional',
            $message,
            ['canal' => 'portal_cliente', 'empresa_id' => $item->empresa_id],
            Filament::auth()->user()
        );

        $this->notifySuccess('Contato registrado. O Portal do Cliente foi aberto para continuar a conversa.');
    }

    public function marcarItemComoResolvido(int $id): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_EXECUTE);

        if (! $item) {
            return;
        }

        if (in_array((string) $item->status, ['concluido', 'cancelado'], true)) {
            $this->notifyError('Este item já está encerrado.');
            $this->closeItemDetailModal();
            return;
        }

        $payload = ['status' => 'concluido'];

        if (CachedSchema::hasColumn('item_controles', 'data_conclusao')) {
            $payload['data_conclusao'] = now();
        }

        if (CachedSchema::hasColumn('item_controles', 'sla_concluido_em') && blank($item->sla_concluido_em)) {
            $payload['sla_concluido_em'] = now();
        }

        if (CachedSchema::hasColumn('item_controles', 'sla_status')) {
            $payload['sla_status'] = 'concluido';
        }

        if (CachedSchema::hasColumn('item_controles', 'status_operacional_at')) {
            $payload['status_operacional_at'] = now();
        }

        $item->update($payload);
        $item->registrarTimeline(
            'conclusao',
            'Item resolvido pelo Centro Operacional',
            'Marcado como resolvido pelo popup de Detalhes da Ação Recomendada.',
            null,
            Filament::auth()->user()
        );

        $this->closeItemDetailModal();
        $this->notifySuccess('Item marcado como resolvido.');
    }

    public function adiarItemResolverAgora(int $id, int $days = 1): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_EXECUTE);

        if (! $item) {
            return;
        }

        if (in_array((string) $item->status, ['concluido', 'cancelado'], true)) {
            $this->notifyError('Itens encerrados não podem ser adiados.');
            return;
        }

        $days = in_array($days, [1, 3, 7], true) ? $days : 1;
        $newDate = now()->addDays($days)->toDateString();

        $payload = ['data_vencimento' => $newDate];
        if (CachedSchema::hasColumn('item_controles', 'status_operacional_at')) {
            $payload['status_operacional_at'] = now();
        }

        $item->update($payload);
        $item->registrarTimeline(
            'prazo_alterado',
            'Prazo adiado pelo Centro Operacional',
            "Prazo adiado por {$days} dia(s) pelo popup de Detalhes da Ação Recomendada.",
            ['dias' => $days, 'novo_prazo' => $newDate],
            Filament::auth()->user()
        );

        $this->notifySuccess('Prazo adiado e registrado na linha do tempo.');
    }

    public function registrarImpedimentoResolverAgora(int $id, string $motivo): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_EXECUTE);

        if (! $item) {
            return;
        }

        $motivos = [
            'cliente' => 'Aguardando retorno ou documento do cliente.',
            'interno' => 'Aguardando validação interna.',
            'governo' => 'Aguardando sistema externo/governo.',
            'documento' => 'Documento obrigatório pendente.',
        ];

        $descricao = $motivos[$motivo] ?? 'Impedimento operacional registrado.';

        $payload = [];
        if (CachedSchema::hasColumn('item_controles', 'bloqueado')) {
            $payload['bloqueado'] = true;
        }
        if (CachedSchema::hasColumn('item_controles', 'status_operacional_at')) {
            $payload['status_operacional_at'] = now();
        }

        if (! empty($payload)) {
            $item->update($payload);
        }

        $item->registrarTimeline(
            'impedimento',
            'Impedimento registrado pelo Centro Operacional',
            $descricao,
            ['motivo' => $motivo],
            Filament::auth()->user()
        );

        $this->notifySuccess('Impedimento registrado.');
    }

    public function registrarSituacaoCliente(int $id, string $situacao): void
    {
        $item = $this->findAllowedItem($id, CentroOperacionalAccess::ACTION_EXECUTE);

        if (! $item) {
            return;
        }

        $situacoes = [
            'respondeu' => [
                'titulo' => 'Cliente respondeu',
                'descricao' => 'Retorno do cliente registrado pelo popup Clientes em Maior Risco.',
                'bloqueado' => false,
                'message' => 'Retorno do cliente registrado.',
            ],
            'documentos_recebidos' => [
                'titulo' => 'Documentos recebidos',
                'descricao' => 'Documentos recebidos ou confirmados pelo popup Clientes em Maior Risco.',
                'bloqueado' => false,
                'message' => 'Documentos recebidos registrados.',
            ],
            'aguardando_cliente' => [
                'titulo' => 'Aguardando cliente',
                'descricao' => 'Pendência mantida em acompanhamento porque depende de retorno do cliente.',
                'bloqueado' => true,
                'message' => 'Situação atualizada para aguardando cliente.',
            ],
            'nao_respondeu' => [
                'titulo' => 'Cliente não respondeu',
                'descricao' => 'Tentativa sem resposta registrada pelo popup Clientes em Maior Risco.',
                'bloqueado' => true,
                'message' => 'Cliente sem resposta registrado.',
            ],
        ];

        $dados = $situacoes[$situacao] ?? null;

        if (! $dados) {
            $this->notifyError('Situação inválida.');
            return;
        }

        $payload = [];

        if (CachedSchema::hasColumn('item_controles', 'bloqueado')) {
            $payload['bloqueado'] = (bool) $dados['bloqueado'];
        }

        if (CachedSchema::hasColumn('item_controles', 'status_operacional_at')) {
            $payload['status_operacional_at'] = now();
        }

        if (! empty($payload)) {
            $item->update($payload);
        }

        $item->registrarTimeline(
            'situacao_cliente',
            $dados['titulo'],
            $dados['descricao'],
            ['situacao' => $situacao],
            Filament::auth()->user()
        );

        $this->notifySuccess($dados['message']);
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


    protected function itemExecutiveSummary(ItemControle $item, ?float $value): string
    {
        $company = $item->empresa?->razao_social ?: 'este cliente';
        $deadline = $this->deadlineLabel($item);
        $valueText = $value !== null ? ' e proteger R$ ' . number_format($value, 2, ',', '.') . ' de impacto financeiro' : '';

        if ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()) {
            return "Resolva este item agora para tirar {$company} do atraso, reduzir cobrança do cliente{$valueText}.";
        }

        if ($item->data_vencimento?->isToday()) {
            return "Trate este item ainda hoje para evitar que {$company} entre em atraso operacional{$valueText}.";
        }

        if (in_array((string) $item->status, ['aguardando_aprovacao', 'em_aprovacao'], true)) {
            return "Decida a aprovação deste item para destravar a próxima etapa de {$company}.";
        }

        if (in_array((string) $item->status, ['correcao_necessaria', 'reprovado'], true)) {
            return "Direcione a correção com clareza para evitar retrabalho recorrente em {$company}.";
        }

        return "Use este painel para decidir a próxima ação, manter dono definido e evitar que {$company} vire risco operacional.";
    }

    protected function itemWhyHere(ItemControle $item, array $checklist, array $timeline): array
    {
        $reasons = [];

        if ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()) {
            $reasons[] = 'O prazo já venceu e precisa de regularização.';
        } elseif ($item->data_vencimento?->isToday()) {
            $reasons[] = 'O prazo vence hoje e precisa de decisão no mesmo dia.';
        } elseif (! $item->data_vencimento) {
            $reasons[] = 'Está sem prazo definido, o que aumenta o risco de perder prioridade.';
        }

        if (! $item->responsavel_id) {
            $reasons[] = 'Não existe responsável claro pela próxima ação.';
        }

        if (in_array((string) $item->prioridade, ['critica', 'urgente', 'alta'], true)) {
            $reasons[] = 'Foi marcado com prioridade elevada na operação.';
        }

        if (in_array((string) $item->status, ['aguardando_aprovacao', 'em_aprovacao'], true)) {
            $reasons[] = 'Está parado aguardando aprovação ou decisão.';
        }

        if (in_array((string) $item->status, ['correcao_necessaria', 'reprovado'], true)) {
            $reasons[] = 'Existe correção/reprovação bloqueando o avanço.';
        }

        $openChecklist = collect($checklist)->where('concluido', false)->count();
        if ($openChecklist > 0) {
            $reasons[] = "Há {$openChecklist} etapa(s) do checklist pendente(s).";
        }

        if (empty($timeline)) {
            $reasons[] = 'Não há movimentação recente registrada.';
        }

        return array_slice($reasons ?: ['Apareceu aqui para garantir acompanhamento antes de virar atraso, retrabalho ou reclamação.'], 0, 5);
    }

    protected function itemOperationalImpact(ItemControle $item, ?float $value, array $relatedClientItems): array
    {
        $department = $item->categoria?->nome ?: ($item->getTipoOuCategoria() ?: 'Operacional');
        $lateRelated = collect($relatedClientItems)->filter(fn (array $related): bool => str_contains(mb_strtolower((string) ($related['vencimento'] ?? '')), 'vencido'))->count();

        return [
            ['label' => 'Cliente impactado', 'value' => $item->empresa?->razao_social ?: 'Sem empresa vinculada'],
            ['label' => 'Departamento afetado', 'value' => $department],
            ['label' => 'Impacto financeiro', 'value' => $value !== null ? 'R$ ' . number_format($value, 2, ',', '.') : 'Não informado; tratar pelo risco operacional'],
            ['label' => 'Pendências recentes do cliente', 'value' => count($relatedClientItems) . ' item(ns)' . ($lateRelated > 0 ? " • {$lateRelated} com sinal de atraso" : '')],
        ];
    }

    protected function itemStalledInfo(ItemControle $item, array $timeline): array
    {
        $lastMove = collect($timeline)->first();
        $lastDate = $item->updated_at ?: $item->created_at;
        $days = $lastDate ? (int) $lastDate->copy()->startOfDay()->diffInDays(now()->startOfDay()) : null;

        return [
            'last_update' => $lastMove['data'] ?? ($lastDate?->format('d/m/Y H:i') ?: 'Sem data registrada'),
            'days' => $days !== null ? ($days === 0 ? 'Movimentado hoje' : "Parado há {$days} dia(s)") : 'Sem histórico suficiente',
            'owner' => $item->responsavel?->nome ?: 'Sem responsável',
        ];
    }

    protected function itemWhatsappUrl(ItemControle $item): ?string
    {
        $phone = $this->empresaTelefone($item);

        if (! $phone) {
            return null;
        }

        return 'https://wa.me/55' . $phone . '?text=' . rawurlencode($this->itemReadyMessage($item));
    }

    protected function itemPortalClienteUrl(ItemControle $item): string
    {
        $params = [];

        if ($item->empresa_id) {
            $params['empresa'] = $item->empresa_id;
        }

        try {
            return PortalCliente::getUrl($params);
        } catch (\Throwable $exception) {
            $query = $params ? ('?' . http_build_query($params)) : '';

            return url('/admin/portal-cliente' . $query);
        }
    }

    protected function empresaTelefone(ItemControle $item): ?string
    {
        $empresa = $item->empresa;

        if (! $empresa) {
            return null;
        }

        foreach (['telefone', 'celular', 'whatsapp', 'phone', 'mobile'] as $field) {
            $value = data_get($empresa, $field);

            if (filled($value)) {
                $digits = preg_replace('/\D+/', '', (string) $value);
                $digits = preg_replace('/^55/', '', $digits);

                if (strlen($digits) >= 10) {
                    return $digits;
                }
            }
        }

        return null;
    }

    protected function itemReadyMessage(ItemControle $item): string
    {
        $company = $item->empresa?->razao_social ?: 'cliente';
        $title = $item->titulo ?: 'pendência operacional';
        $deadline = $item->data_vencimento?->format('d/m/Y') ?: 'o quanto antes';

        if (in_array((string) $item->status, ['aguardando_aprovacao', 'em_aprovacao'], true)) {
            return "Olá. Precisamos de uma decisão sobre {$title} do cliente {$company}. Pode validar e aprovar ou apontar a correção necessária até {$deadline}?";
        }

        if (in_array((string) $item->status, ['correcao_necessaria', 'reprovado'], true)) {
            return "Olá. A pendência {$title} do cliente {$company} precisa de correção para avançar. Pode ajustar e retornar ainda hoje, por favor?";
        }

        return "Olá. Identificamos a pendência {$title} do cliente {$company}. Precisamos resolver até {$deadline} para evitar atraso, retrabalho ou cobrança do cliente. Pode verificar e retornar, por favor?";
    }

    protected function itemBlockers(ItemControle $item, array $checklist, array $timeline): array
    {
        $blockers = [];

        if (! $item->responsavel_id) {
            $blockers[] = 'Sem responsável definido.';
        }

        if (in_array((string) $item->status, ['aguardando_aprovacao', 'em_aprovacao'], true)) {
            $blockers[] = 'Aguardando aprovação para avançar.';
        }

        if (in_array((string) $item->status, ['correcao_necessaria', 'reprovado'], true)) {
            $blockers[] = 'Correção/reprovação pendente de tratamento.';
        }

        foreach (collect($checklist)->where('concluido', false)->take(3) as $check) {
            $blockers[] = 'Checklist pendente: ' . ($check['titulo'] ?? 'Etapa operacional');
        }

        if (empty($timeline)) {
            $blockers[] = 'Sem histórico recente para explicar o andamento.';
        }

        return $blockers ?: ['Nenhum bloqueador claro encontrado. Abrir cadastro apenas se precisar validar dados técnicos.'];
    }

    protected function itemDoneDefinition(ItemControle $item): array
    {
        $status = (string) $item->status;

        if (in_array($status, ['aguardando_aprovacao', 'em_aprovacao'], true)) {
            return ['Aprovação registrada.', 'Correção solicitada quando houver erro.', 'Próxima etapa liberada com responsável definido.'];
        }

        if (in_array($status, ['correcao_necessaria', 'reprovado'], true)) {
            return ['Correção realizada pelo responsável.', 'Checklist pendente revisado.', 'Status atualizado para andamento, aprovado ou concluído.'];
        }

        return ['Pendência tratada ou encaminhada.', 'Responsável e prazo confirmados.', 'Status atualizado para concluído/aprovado ou próxima ação documentada.'];
    }

    protected function itemUrgencyScore(ItemControle $item, array $checklist, array $timeline, array $relatedClientItems): array
    {
        $score = 20;
        $reasons = [];

        if ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()) {
            $score += 35;
            $reasons[] = 'Prazo vencido +35';
        } elseif ($item->data_vencimento?->isToday()) {
            $score += 30;
            $reasons[] = 'Vence hoje +30';
        } elseif (! $item->data_vencimento) {
            $score += 15;
            $reasons[] = 'Sem prazo +15';
        }

        if (! $item->responsavel_id) {
            $score += 20;
            $reasons[] = 'Sem responsável +20';
        }

        if (in_array((string) $item->prioridade, ['critica', 'urgente', 'alta'], true)) {
            $score += 20;
            $reasons[] = 'Prioridade alta +20';
        }

        if (in_array((string) $item->status, ['aguardando_aprovacao', 'em_aprovacao', 'correcao_necessaria', 'reprovado'], true)) {
            $score += 15;
            $reasons[] = 'Status bloqueante +15';
        }

        $openChecklist = collect($checklist)->where('concluido', false)->count();
        if ($openChecklist > 0) {
            $score += min(10, $openChecklist * 3);
            $reasons[] = "Checklist pendente +" . min(10, $openChecklist * 3);
        }

        if (count($relatedClientItems) >= 3) {
            $score += 10;
            $reasons[] = 'Cliente com várias pendências +10';
        }

        $score = min(100, $score);
        $tone = $score >= 80 ? 'danger' : ($score >= 60 ? 'warning' : 'info');

        return [
            'value' => $score,
            'tone' => $tone,
            'label' => $score >= 80 ? 'Urgência máxima' : ($score >= 60 ? 'Alta atenção' : 'Acompanhar'),
            'reasons' => array_slice($reasons ?: ['Risco calculado pelo contexto operacional'], 0, 5),
        ];
    }

    protected function itemClientRiskSummary(ItemControle $item, array $checklist, array $timeline, array $relatedClientItems): array
    {
        $summary = [];
        $openRelated = count($relatedClientItems);
        $openChecklist = collect($checklist)->where('concluido', false)->count();

        if ($openRelated > 0) {
            $summary[] = $openRelated . ' pendência(s) relacionada(s) aberta(s).';
        }

        if ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday()) {
            $summary[] = 'Obrigação principal com prazo vencido.';
        } elseif ($item->data_vencimento?->isToday()) {
            $summary[] = 'Obrigação principal vence hoje.';
        } elseif ($item->data_vencimento) {
            $summary[] = 'Prazo principal: ' . $item->data_vencimento->format('d/m/Y') . '.';
        } else {
            $summary[] = 'Obrigação principal sem prazo cadastrado.';
        }

        if ($openChecklist > 0) {
            $summary[] = $openChecklist . ' etapa(s) de checklist pendente(s).';
        }

        if (! $item->responsavel_id) {
            $summary[] = 'Sem responsável definido.';
        }

        if (in_array((string) $item->status, ['aguardando_aprovacao', 'em_aprovacao', 'correcao_necessaria', 'reprovado'], true)) {
            $summary[] = 'Status atual exige decisão ou correção.';
        }

        if (CachedSchema::hasColumn('item_controles', 'bloqueado') && (bool) $item->bloqueado) {
            $summary[] = 'Item marcado como bloqueado.';
        }

        $lastDate = $item->updated_at ?: $item->created_at;
        if ($lastDate) {
            $days = (int) $lastDate->copy()->startOfDay()->diffInDays(now()->startOfDay());
            if ($days >= 2) {
                $summary[] = 'Sem movimentação há ' . $days . ' dia(s).';
            }
        }

        return array_slice(array_values(array_unique($summary)), 0, 5);
    }

    protected function itemClientRelationship(ItemControle $item): array
    {
        $lastContact = null;

        if (CachedSchema::hasTable('item_controle_timeline')) {
            try {
                $lastContact = $item->timelines()
                    ->latest('id')
                    ->get()
                    ->first(function ($entry): bool {
                        $haystack = mb_strtolower(trim(((string) $entry->tipo) . ' ' . ((string) $entry->titulo) . ' ' . ((string) $entry->descricao)));

                        return str_contains($haystack, 'contato')
                            || str_contains($haystack, 'cliente respondeu')
                            || str_contains($haystack, 'documentos recebidos')
                            || str_contains($haystack, 'whatsapp')
                            || str_contains($haystack, 'portal');
                    });
            } catch (\Throwable $exception) {
                $lastContact = null;
            }
        }

        $lastDate = $lastContact?->created_at ?: ($item->updated_at ?: $item->created_at);
        $days = $lastDate ? (int) $lastDate->copy()->startOfDay()->diffInDays(now()->startOfDay()) : null;
        $channel = 'Ainda não registrado';

        if ($lastContact) {
            $text = mb_strtolower(trim(((string) $lastContact->titulo) . ' ' . ((string) $lastContact->descricao)));

            if (str_contains($text, 'portal')) {
                $channel = 'Portal do Cliente';
            } elseif (str_contains($text, 'whatsapp')) {
                $channel = 'WhatsApp';
            } elseif (str_contains($text, 'documento')) {
                $channel = 'Documentos';
            } else {
                $channel = 'Registro interno';
            }
        }

        return [
            'last_contact' => $lastContact?->created_at?->format('d/m/Y H:i') ?: 'Sem contato registrado',
            'last_activity' => $lastDate?->format('d/m/Y H:i') ?: 'Sem movimentação registrada',
            'silence' => $days !== null ? ($days === 0 ? 'Movimentado hoje' : $days . ' dia(s) sem nova movimentação') : 'Sem informação suficiente',
            'channel' => $channel,
        ];
    }

    protected function itemCriticalClientInfo(ItemControle $item, array $relatedClientItems): array
    {
        $open = count($relatedClientItems);
        $isCritical = $open >= 3 || ($item->data_vencimento?->isPast() && ! $item->data_vencimento?->isToday());

        return [
            'show' => $isCritical,
            'open' => $open,
            'risk' => $isCritical ? 'Alto' : 'Monitorado',
            'text' => $isCritical
                ? 'Cliente com sinais de acúmulo operacional. Resolva este item e confira se há outras pendências abertas.'
                : 'Cliente sem acúmulo crítico detectado neste modal.',
        ];
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
