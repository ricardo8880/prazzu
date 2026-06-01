<?php

namespace App\Filament\Resources\ItemControles\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\Responsavel;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateItemControle extends CreateRecord
{
    protected static string $resource = ItemControleResource::class;

    protected function beforeCreate(): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        if (! $user->hasEmpresaVinculada()) {
            Notification::make()
                ->title('Seu usuário não possui empresa vinculada.')
                ->danger()
                ->send();

            $this->halt();
        }

        if ($user->isAdminEmpresa()) {
            return;
        }

        if ($user->isGestor()) {
            $temEquipe = Responsavel::query()
                ->where('empresa_id', $user->empresa_id)
                ->where('gestor_user_id', $user->id)
                ->exists();

            if (! $temEquipe) {
                Notification::make()
                    ->title('Este gestor ainda não possui responsáveis vinculados à equipe.')
                    ->danger()
                    ->send();

                $this->halt();
            }

            return;
        }

        if (! $user->responsavel) {
            Notification::make()
                ->title('Seu usuário não está vinculado a um responsável.')
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
            $responsavel = Responsavel::query()->find($data['responsavel_id'] ?? null);

            if (! $responsavel) {
                abort(403, 'Responsável inválido.');
            }

            $data['empresa_id'] = $responsavel->empresa_id;

            return $data;
        }

        if (! $user->hasEmpresaVinculada()) {
            abort(403, 'Seu usuário não possui empresa vinculada.');
        }

        $data['empresa_id'] = $user->empresa_id;

        if ($user->isAdminEmpresa()) {
            $responsavelId = isset($data['responsavel_id']) ? (int) $data['responsavel_id'] : null;

            if (! ItemControleResource::canUserAssignResponsavel($user, $responsavelId)) {
                abort(403, 'Você só pode criar itens para responsáveis da sua empresa.');
            }

            return $data;
        }

        if ($user->isGestor()) {
            $responsavelId = isset($data['responsavel_id']) ? (int) $data['responsavel_id'] : null;

            if (! ItemControleResource::canUserAssignResponsavel($user, $responsavelId)) {
                abort(403, 'Você só pode criar itens para responsáveis da sua equipe.');
            }

            return $data;
        }

        $responsavelId = ItemControleResource::getDefaultResponsavelIdForUser($user);

        if (! $responsavelId) {
            abort(403, 'Seu usuário não possui responsável vinculado.');
        }

        $data['responsavel_id'] = $responsavelId;

        return $data;
    }
}
