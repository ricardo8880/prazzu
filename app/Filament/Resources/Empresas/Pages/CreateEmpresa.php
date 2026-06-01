<?php

namespace App\Filament\Resources\Empresas\Pages;

use App\Filament\Resources\Empresas\EmpresaResource;
use App\Models\Configuracao;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateEmpresa extends CreateRecord
{
    protected static string $resource = EmpresaResource::class;

    protected array $adminData = [];

    protected function beforeCreate(): void
    {
        $user = Filament::auth()->user();

        if (! $user?->isSuperAdmin()) {
            abort(403, 'Somente super admin pode criar empresas.');
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->adminData = [
            'criar_admin' => (bool) ($data['criar_admin'] ?? false),
            'admin_nome' => $data['admin_nome'] ?? null,
            'admin_email' => $data['admin_email'] ?? null,
            'admin_password' => $data['admin_password'] ?? null,
        ];

        unset(
            $data['criar_admin'],
            $data['admin_nome'],
            $data['admin_email'],
            $data['admin_password'],
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        Configuracao::forEmpresaId($this->record->id);

        if (($this->adminData['criar_admin'] ?? false) === true) {
            User::query()->create([
                'name' => $this->adminData['admin_nome'],
                'email' => $this->adminData['admin_email'],
                'password' => Hash::make($this->adminData['admin_password']),
                'role' => 'admin',
                'empresa_id' => $this->record->id,
            ]);

            Notification::make()
                ->title('Empresa e admin criados com sucesso.')
                ->success()
                ->send();

            return;
        }

        Notification::make()
            ->title('Empresa criada com sucesso.')
            ->success()
            ->send();
    }
}