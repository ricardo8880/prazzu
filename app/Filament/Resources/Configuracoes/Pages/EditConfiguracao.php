<?php

namespace App\Filament\Resources\Configuracoes\Pages;

use App\Filament\Resources\Configuracoes\ConfiguracaoResource;
use App\Models\Configuracao;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditConfiguracao extends EditRecord
{
    protected static string $resource = ConfiguracaoResource::class;

    public function getRecord(): Configuracao
    {
        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->isSuperAdmin()) {
            $configuracao = Configuracao::query()
                ->with(['empresa:id,razao_social'])
                ->orderBy('empresa_id')
                ->first();

            if ($configuracao) {
                return $configuracao;
            }

            $empresa = \App\Models\Empresa::query()->orderBy('id')->first();

            if (! $empresa) {
                abort(404, 'Cadastre uma empresa antes de acessar as configurações.');
            }

            return Configuracao::forEmpresaId($empresa->id);
        }

        if (! $user->isAdminEmpresa()) {
            abort(403, 'Somente admin da empresa pode editar configurações.');
        }

        if (! $user->hasEmpresaVinculada()) {
            abort(403, 'Seu usuário não possui empresa vinculada.');
        }

        return Configuracao::forEmpresaId($user->empresa_id);
    }

    protected function beforeSave(): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if (! $user->isSuperAdmin() && ! $user->isAdminEmpresa()) {
            abort(403, 'Você não tem permissão para alterar configurações.');
        }

        if ($user->isAdminEmpresa() && (int) $this->record->empresa_id !== (int) $user->empresa_id) {
            abort(403, 'Você só pode editar configurações da sua empresa.');
        }
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Configurações salvas com sucesso.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
