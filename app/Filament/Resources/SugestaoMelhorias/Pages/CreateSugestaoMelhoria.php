<?php

namespace App\Filament\Resources\SugestaoMelhorias\Pages;

use App\Filament\Resources\SugestaoMelhorias\SugestaoMelhoriaResource;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSugestaoMelhoria extends CreateRecord
{
    protected static string $resource = SugestaoMelhoriaResource::class;

    protected function beforeCreate(): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->isSuperAdmin()) {
            Notification::make()
                ->title('Super admin não envia contribuições nesta área.')
                ->body('Esta área é exclusiva para feedback enviado pelas empresas.')
                ->danger()
                ->send();

            $this->halt();
        }

        if (! $user->hasEmpresaVinculada()) {
            Notification::make()
                ->title('Seu usuário não possui empresa vinculada.')
                ->body('Para enviar uma contribuição, o usuário precisa estar vinculado a uma empresa.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->isSuperAdmin()) {
            abort(403, 'Super admin não pode criar sugestões nesta área.');
        }

        if (! $user->hasEmpresaVinculada()) {
            abort(403, 'Seu usuário não possui empresa vinculada.');
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