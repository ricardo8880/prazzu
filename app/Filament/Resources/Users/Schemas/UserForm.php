<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Empresa;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function make(Schema $schema): Schema
    {
        $authUser = Filament::auth()->user();

        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255)
                    ->trim(),

                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class, 'email', ignoreRecord: true)
                    ->trim(),

                Select::make('empresa_id')
                    ->label('Empresa')
                    ->required(fn (): bool => $authUser?->isSuperAdmin() !== true)
                    ->searchable()
                    ->native(false)
                    ->visible(fn (): bool => $authUser?->isSuperAdmin() === true)
                    ->default(fn (): ?int => $authUser?->isSuperAdmin() ? null : $authUser?->empresa_id)
                    ->dehydrated(true)
                    ->getSearchResultsUsing(function (string $search): array {
                        return Empresa::query()
                            ->select(['id', 'razao_social', 'nome_fantasia', 'cnpj'])
                            ->where(function ($query) use ($search): void {
                                $query->where('razao_social', 'like', "%{$search}%")
                                    ->orWhere('nome_fantasia', 'like', "%{$search}%")
                                    ->orWhere('cnpj', 'like', "%{$search}%");
                            })
                            ->orderBy('razao_social')
                            ->limit(50)
                            ->pluck('razao_social', 'id')
                            ->toArray();
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => blank($value) ? null : Empresa::find($value)?->razao_social)
                    ->helperText('Super admin pode escolher qualquer empresa. Admin cria usuários somente para a própria empresa.'),

                Select::make('role')
                    ->label('Perfil')
                    ->required()
                    ->native(false)
                    ->default('user')
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                        if (blank($get('perfil_contabil'))) {
                            $set('perfil_contabil', User::perfilContabilPadraoPorRole($state));
                        }
                    })
                    ->options(function () use ($authUser): array {
                        if ($authUser?->isSuperAdmin()) {
                            return [
                                'super_admin' => 'Super Admin',
                                'admin' => 'Admin da Empresa',
                                'gestor' => 'Gestor',
                                'user' => 'Usuário',
                            ];
                        }

                        return [
                            'admin' => 'Admin da Empresa',
                            'gestor' => 'Gestor',
                            'user' => 'Usuário',
                        ];
                    })
                    ->rules([
                        function () use ($authUser) {
                            return function (string $attribute, $value, \Closure $fail) use ($authUser): void {
                                if (! $authUser) {
                                    $fail('Usuário não autenticado.');
                                    return;
                                }

                                if ($value === 'super_admin' && ! $authUser->isSuperAdmin()) {
                                    $fail('Somente super admin pode criar ou editar outro super admin.');
                                }
                            };
                        },
                    ]),


                Select::make('perfil_contabil')
                    ->label('Perfil contábil')
                    ->native(false)
                    ->searchable()
                    ->options(User::perfilContabilOptions())
                    ->default(fn (Get $get): ?string => User::perfilContabilPadraoPorRole($get('role')) ?? 'assistente')
                    ->helperText('Define a função real do usuário no escritório. Neste lote, o campo é cadastral; a sidebar e os bloqueios por URL entram nos próximos lotes.'),

                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->helperText('Na edição, deixe em branco para manter a senha atual.'),
            ])
            ->columns(2);
    }
}
