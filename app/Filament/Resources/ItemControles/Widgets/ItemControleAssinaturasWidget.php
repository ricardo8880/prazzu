<?php

namespace App\Filament\Resources\ItemControles\Widgets;

use App\Models\ItemControle;
use App\Services\ItemControleAssinaturaService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class ItemControleAssinaturasWidget extends Widget
{
    public ?ItemControle $record = null;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.resources.item-controles.widgets.item-controle-assinaturas-widget';

    protected $listeners = [
        'item-controle-updated' => '$refresh',
    ];

    public function getViewData(): array
    {
        $item = $this->getItem();

        return [
            'item' => $item,
            'assinatura' => $item ? app(ItemControleAssinaturaService::class)->status($item) : null,
            'podeGerenciar' => $this->podeGerenciar(),
        ];
    }

    public function reenviarAssinatura(): void
    {
        $item = $this->getItem();

        if (! $item || ! $this->podeGerenciar()) {
            abort(403, 'Você não tem permissão para reenviar assinatura deste item.');
        }

        app(ItemControleAssinaturaService::class)->reenviar($item);

        $this->dispatch('item-controle-updated', id: $item->id);

        Notification::make()
            ->title('Assinatura reenviada')
            ->body('O portal de assinatura foi ativado e o link ficou disponível para envio ao assinante.')
            ->success()
            ->send();
    }

    public function consultarStatus(): void
    {
        $item = $this->getItem();

        if (! $item || ! $this->podeGerenciar()) {
            abort(403, 'Você não tem permissão para consultar assinatura deste item.');
        }

        app(ItemControleAssinaturaService::class)->consultar($item);

        $this->dispatch('item-controle-updated', id: $item->id);

        Notification::make()
            ->title('Status consultado')
            ->body('A situação da assinatura foi atualizada com os registros disponíveis.')
            ->success()
            ->send();
    }

    private function getItem(): ?ItemControle
    {
        return $this->record?->fresh(['assinaturas.user', 'empresa', 'responsavel']);
    }

    private function podeGerenciar(): bool
    {
        $user = Filament::auth()->user();
        $item = $this->record;

        return (bool) ($user && $item && $item->canBeModifiedBy($user));
    }
}
