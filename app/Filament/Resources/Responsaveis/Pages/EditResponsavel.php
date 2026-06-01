<?php

namespace App\Filament\Resources\Responsaveis\Pages;

use App\Filament\Resources\Responsaveis\ResponsavelResource;
use App\Models\Responsavel;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Throwable;

class EditResponsavel extends EditRecord
{
    protected static string $resource = ResponsavelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('redefinirSenha')
                ->label('Redefinir senha')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->visible(fn (): bool => filled($this->record?->user_id))
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
                    /** @var Responsavel $record */
                    $record = $this->record;

                    $usuario = $record->user;

                    if (! $usuario) {
                        Notification::make()
                            ->title('Este responsável não possui usuário vinculado.')
                            ->danger()
                            ->send();

                        return;
                    }

                    if (($data['modo'] ?? null) === 'manual') {
                        if (($data['senha'] ?? null) !== ($data['senha_confirmation'] ?? null)) {
                            Notification::make()
                                ->title('As senhas não conferem.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $usuario->update([
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
                            'email' => $usuario->email,
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

            DeleteAction::make()
                ->before(function (): void {
                    /** @var Responsavel $record */
                    $record = $this->record;

                    if ($record->itemControles()->limit(1)->exists()) {
                        Notification::make()
                            ->title('Este responsável possui itens de controle vinculados e não pode ser excluído.')
                            ->danger()
                            ->send();

                        $this->halt();
                    }
                }),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Responsável atualizado com sucesso.');
    }
}
