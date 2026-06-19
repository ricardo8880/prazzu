<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UsersTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label('Usuário')
                    ->description(fn (User $record): string => $record->email)
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(false),

                TextColumn::make('role')
                    ->label('Perfil')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin da Empresa',
                        'gestor' => 'Gestor',
                        'user' => 'Usuário',
                        default => (string) $state,
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'gestor' => 'info',
                        'user' => 'gray',
                        default => 'gray',
                    }),


                TextColumn::make('perfil_contabil')
                    ->label('Perfil contábil')
                    ->badge()
                    ->placeholder('Não definido')
                    ->formatStateUsing(fn (?string $state): string => User::perfilContabilOptions()[$state] ?? 'Não definido')
                    ->color(fn (?string $state): string => match ($state) {
                        'socio' => 'warning',
                        'gestor' => 'info',
                        'contador' => 'success',
                        'fiscal' => 'primary',
                        'departamento_pessoal' => 'purple',
                        'assistente' => 'gray',
                        'cliente' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('empresa.razao_social')
                    ->label('Empresa')
                    ->placeholder('Global')
                    ->limit(40)
                    ->tooltip(fn (User $record): ?string => $record->empresa?->razao_social),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                SelectFilter::make('perfil_contabil')
                    ->label('Perfil contábil')
                    ->options(User::perfilContabilOptions()),

                SelectFilter::make('role')
                    ->label('Perfil')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin da Empresa',
                        'gestor' => 'Gestor',
                        'user' => 'Usuário',
                        'guest' => 'Convidado',
                    ]),
            ])
            ->recordActions([
                Action::make('resetSenha')
                    ->label('Redefinir senha')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(function (User $record): bool {
                        $auth = Filament::auth()->user();

                        if ($auth?->isSuperAdmin()) {
                            return true;
                        }

                        return $auth?->empresa_id === $record->empresa_id;
                    })
                    ->form([
                        Select::make('modo')
                            ->label('Como redefinir?')
                            ->options([
                                'link' => 'Enviar link por e-mail',
                                'manual' => 'Definir nova senha',
                            ])
                            ->default('link')
                            ->required()
                            ->live(),

                        TextInput::make('senha')
                            ->label('Nova senha')
                            ->password()
                            ->visible(fn ($get) => $get('modo') === 'manual')
                            ->required(fn ($get) => $get('modo') === 'manual'),

                        TextInput::make('senha_confirmation')
                            ->label('Confirmar senha')
                            ->password()
                            ->visible(fn ($get) => $get('modo') === 'manual')
                            ->required(fn ($get) => $get('modo') === 'manual'),
                    ])
                    ->action(function (User $record, array $data): void {
                        if ($data['modo'] === 'link') {
                            Password::sendResetLink([
                                'email' => $record->email,
                            ]);

                            Notification::make()
                                ->title('Link enviado com sucesso')
                                ->success()
                                ->send();

                            return;
                        }

                        if ($data['modo'] === 'manual') {
                            if ($data['senha'] !== $data['senha_confirmation']) {
                                Notification::make()
                                    ->title('As senhas não coincidem')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $record->update([
                                'password' => Hash::make($data['senha']),
                            ]);

                            Notification::make()
                                ->title('Senha atualizada com sucesso')
                                ->success()
                                ->send();
                        }
                    }),

                ActionGroup::make([
                    DeleteAction::make()
                        ->label('Excluir')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->visible(fn (User $record): bool => Filament::auth()->user()?->isSuperAdmin() === true),
                ])
                    ->label('Mais')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ]);
    }
}
