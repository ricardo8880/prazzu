<?php

namespace App\Filament\Resources\SugestaoMelhorias\Pages;

use App\Filament\Resources\SugestaoMelhorias\SugestaoMelhoriaResource;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class CreateSugestaoMelhoria extends CreateRecord
{
    protected static string $resource = SugestaoMelhoriaResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Enviar contribuição';
    }

    public function getHeading(): string
    {
        return 'Conte sua dor, sugestão ou ideia';
    }

    public function getSubheading(): ?string
    {
        return 'Use este espaço para explicar o que está atrapalhando sua rotina ou o que poderia tornar o Prazzu melhor para várias empresas.';
    }

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return parent::defaultForm($schema)
            ->columns(['default' => 1]);
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Enviar contribuição')
            ->icon('heroicon-o-paper-airplane');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Voltar');
    }

    protected function beforeCreate(): void
    {
        if (! Filament::auth()->check()) {
            abort(403, 'Usuário não autenticado.');
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        $data['user_id'] = $user->id;
        $data['empresa_id'] = $user->empresa_id;
        $data['status'] = 'aberta';

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Contribuição enviada com sucesso.')
            ->body('Obrigado por ajudar a evoluir o Prazzu. Sua contribuição será avaliada por impacto, recorrência e escalabilidade.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return SugestaoMelhoriaResource::getUrl('index');
    }
}