<?php

namespace App\Filament\Resources\Empresas\Pages;

use App\Filament\Resources\Empresas\EmpresaResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditEmpresa extends EditRecord
{
    protected static string $resource = EmpresaResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->isAdminEmpresa() && (int) $user->empresa_id === (int) $this->record->id) {
            return;
        }

        abort(403, 'Você não tem permissão para editar esta empresa.');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}