<?php

namespace App\Filament\Pages;


use App\Support\CachedSchema;
use App\Models\ItemControle;
use App\Services\CentroOperacionalService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class CentroOperacional extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-command-line';
    protected static string | UnitEnum | null $navigationGroup = 'Trabalho';
    protected static ?string $navigationLabel = 'Centro Operacional';
    protected static ?string $title = 'Centro Operacional';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.centro-operacional';

    public function getHeading(): string
    {
        return '';
    }

    protected function getViewData(): array
    {
        return [
            'data' => app(CentroOperacionalService::class)->dashboard(Filament::auth()->user()),
        ];
    }

    public function aprovar(int $id): void
    {
        $item = $this->findAllowedItem($id);

        if (! $item) {
            $this->notifyError('Não foi possível aprovar este item.');
            return;
        }

        $item->aprovar(Filament::auth()->user(), 'Aprovado pelo Centro Operacional.');
        $this->notifySuccess('Item aprovado com sucesso.');
    }

    public function enviarParaCorrecao(int $id): void
    {
        $item = $this->findAllowedItem($id);

        if (! $item) {
            $this->notifyError('Não foi possível enviar este item para correção.');
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

    public function marcarFaturado(int $id): void
    {
        $item = $this->findAllowedItem($id);

        if (! $item) {
            $this->notifyError('Não foi possível faturar este item.');
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
        $item = $this->findAllowedItem($id);

        if (! $item) {
            $this->notifyError('Não foi possível marcar este item como pago.');
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

    protected function findAllowedItem(int $id): ?ItemControle
    {
        return ItemControle::query()
            ->visibleForUser(Filament::auth()->user())
            ->whereKey($id)
            ->first();
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
