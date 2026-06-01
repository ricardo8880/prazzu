<?php

namespace App\Filament\Resources\Responsaveis\Pages;

use App\Filament\Resources\Responsaveis\ResponsavelResource;
use App\Models\Empresa;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class CreateResponsavel extends CreateRecord
{
    protected static string $resource = ResponsavelResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $authUser = Filament::auth()->user();

        $empresaId = $authUser?->isSuperAdmin()
            ? ($data['empresa_id'] ?? null)
            : $authUser?->empresa_id;

        $empresa = $authUser?->isSuperAdmin()
            ? Empresa::find($empresaId)
            : $authUser?->empresa;

        if (! $empresa || ! $empresa->isAtivo()) {
            Notification::make()
                ->title('Empresa inativa')
                ->body('Não é possível cadastrar responsáveis para uma empresa inativa.')
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        if (! $authUser?->isSuperAdmin() && $empresa->atingiuLimiteUsuarios()) {
            Notification::make()
                ->title('🚫 Limite do plano atingido')
                ->body('Você atingiu o limite de usuários do plano atual. Para cadastrar mais pessoas, solicite a alteração do plano.')
                ->danger()
                ->persistent()
                ->actions([
                    Action::make('verPlanos')
                        ->label('Ver planos')
                        ->button()
                        ->url('/admin/empresas?planos=1'),
                ])
                ->send();

            $this->halt();
        }

        $perfilAcesso = $data['perfil_acesso'] ?? 'user';
        $modoSenha = $data['modo_senha'] ?? 'usuario_define';

        if (! empty($data['user_id'])) {
            $usuario = User::find($data['user_id']);

            if ($usuario) {
                $data['empresa_id'] = $empresaId;
                $data['user_id'] = $usuario->id;

                unset(
                    $data['perfil_acesso'],
                    $data['modo_senha'],
                    $data['senha_inicial'],
                    $data['senha_inicial_confirmation']
                );

                return $data;
            }
        }

        $senha = $modoSenha === 'admin_define'
            ? ($data['senha_inicial'] ?? Str::random(32))
            : Str::random(32);

        $novoUsuario = User::create([
            'name' => $data['nome'],
            'email' => $data['email'],
            'password' => Hash::make($senha),
            'role' => $perfilAcesso,
            'empresa_id' => $empresaId,
        ]);

        if ($modoSenha === 'usuario_define') {
            try {
                Password::sendResetLink([
                    'email' => $novoUsuario->email,
                ]);

                Notification::make()
                    ->title('Link de criação de senha enviado')
                    ->body('O usuário receberá um e-mail para criar a própria senha de acesso.')
                    ->success()
                    ->send();
            } catch (Throwable $e) {
                Notification::make()
                    ->title('Usuário criado, mas o e-mail não foi enviado')
                    ->body('Verifique a configuração de e-mail do sistema. O usuário foi criado, mas ainda precisa receber o link de criação de senha.')
                    ->warning()
                    ->persistent()
                    ->send();
            }
        }

        $data['empresa_id'] = $empresaId;
        $data['user_id'] = $novoUsuario->id;

        if ($perfilAcesso === 'gestor') {
            $data['gestor_user_id'] = null;
        }

        unset(
            $data['perfil_acesso'],
            $data['modo_senha'],
            $data['senha_inicial'],
            $data['senha_inicial_confirmation']
        );

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Responsável criado com sucesso.');
    }
}
