<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Throwable;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $user = Filament::auth()->user();
        $recordUser = $this->getCurrentUserRecord();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        if (
            $user->isAdminEmpresa()
            && (int) $recordUser->empresa_id === (int) $user->empresa_id
            && $recordUser->role !== 'super_admin'
        ) {
            return;
        }

        abort(403, 'Você não tem permissão para editar este usuário.');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('redefinirSenha')
                ->label('Redefinir senha')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->form([
                    Select::make('modo')
                        ->label('Como deseja redefinir?')
                        ->options([
                            'link' => 'Enviar link para o usuário criar nova senha',
                            'manual' => 'Definir senha manualmente agora',
                        ])
                        ->default('link')
                        ->required()
                        ->native(false)
                        ->live(),

                    TextInput::make('senha')
                        ->label('Nova senha')
                        ->password()
                        ->revealable()
                        ->minLength(6)
                        ->required(fn ($get): bool => $get('modo') === 'manual')
                        ->visible(fn ($get): bool => $get('modo') === 'manual'),

                    TextInput::make('senha_confirmation')
                        ->label('Confirmar nova senha')
                        ->password()
                        ->revealable()
                        ->required(fn ($get): bool => $get('modo') === 'manual')
                        ->visible(fn ($get): bool => $get('modo') === 'manual'),
                ])
                ->action(function (array $data): void {
                    $authUser = Filament::auth()->user();
                    $recordUser = $this->getCurrentUserRecord();

                    if (! $authUser) {
                        abort(403, 'Usuário não autenticado.');
                    }

                    if (
                        ! $authUser->isSuperAdmin()
                        && (
                            ! $authUser->isAdminEmpresa()
                            || (int) $authUser->empresa_id !== (int) $recordUser->empresa_id
                            || $recordUser->role === 'super_admin'
                        )
                    ) {
                        abort(403, 'Você não tem permissão para redefinir a senha deste usuário.');
                    }

                    if (($data['modo'] ?? null) === 'manual') {
                        if (($data['senha'] ?? null) !== ($data['senha_confirmation'] ?? null)) {
                            Notification::make()
                                ->title('As senhas não conferem.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $recordUser->update([
                            'password' => Hash::make($data['senha']),
                        ]);

                        Notification::make()
                            ->title('Senha redefinida com sucesso.')
                            ->success()
                            ->send();

                        return;
                    }

                    try {
                        Password::sendResetLink([
                            'email' => $recordUser->email,
                        ]);

                        Notification::make()
                            ->title('Link de redefinição enviado.')
                            ->body('O usuário receberá um e-mail para criar uma nova senha.')
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Não foi possível enviar o e-mail.')
                            ->body('Verifique a configuração de e-mail do sistema.')
                            ->warning()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Filament::auth()->user();
        $recordUser = $this->getCurrentUserRecord();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->isSuperAdmin()) {
            if (($data['role'] ?? null) === 'super_admin') {
                $data['empresa_id'] = null;
            }

            if (($data['role'] ?? null) !== 'super_admin' && blank($data['empresa_id'] ?? null)) {
                abort(403, 'Usuários que não são super admin precisam de empresa vinculada.');
            }

            return $data;
        }

        if (! $user->isAdminEmpresa()) {
            abort(403, 'Você não tem permissão para editar usuários.');
        }

        if ((int) $recordUser->empresa_id !== (int) $user->empresa_id) {
            abort(403, 'Você só pode editar usuários da sua empresa.');
        }

        if (($data['role'] ?? null) === 'super_admin') {
            abort(403, 'Admin de empresa não pode definir super admin.');
        }

        $data['empresa_id'] = $user->empresa_id;

        return $data;
    }

    protected function getCurrentUserRecord(): User
    {
        /** @var User $record */
        $record = $this->record;

        return $record;
    }
}
