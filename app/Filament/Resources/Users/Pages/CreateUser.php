<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeCreate(): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if (! $user->isSuperAdmin() && ! $user->isAdminEmpresa()) {
            abort(403, 'Você não tem permissão para criar usuários.');
        }

        if ($user->isAdminEmpresa() && ! $user->hasEmpresaVinculada()) {
            Notification::make()
                ->title('Seu usuário não possui empresa vinculada.')
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
            if (($data['role'] ?? null) !== 'super_admin' && blank($data['empresa_id'] ?? null)) {
                abort(403, 'Usuários que não são super admin precisam de empresa vinculada.');
            }

            if (($data['role'] ?? null) === 'super_admin') {
                $data['empresa_id'] = null;
            }

            return $data;
        }

        if (! $user->isAdminEmpresa()) {
            abort(403, 'Você não tem permissão para criar usuários.');
        }

        if (($data['role'] ?? null) === 'super_admin') {
            abort(403, 'Admin de empresa não pode criar super admin.');
        }

        $data['empresa_id'] = $user->empresa_id;

        return $data;
    }
}
