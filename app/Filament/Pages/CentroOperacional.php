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
        $this->statusFilter = 'all';

        $this->redirect(ItemControleResource::getUrl('index'));
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
        if (! $this->delegateItemId) {
            return [];
        }

        $item = ItemControle::query()
            ->visibleForUser(Filament::auth()->user())
            ->whereKey($this->delegateItemId)
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
