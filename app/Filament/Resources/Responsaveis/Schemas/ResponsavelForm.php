<?php

namespace App\Filament\Resources\Responsaveis\Schemas;

use App\Models\Empresa;
use App\Models\Responsavel;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class ResponsavelForm
{
    public static function make(Schema $schema): Schema
    {
        $user = Filament::auth()->user();

        return $schema
            ->components([
                Select::make('empresa_id')
                    ->label('Empresa')
                    ->getSearchResultsUsing(fn (string $search): array => Empresa::query()
                        ->where('razao_social', 'like', "%{$search}%")
                        ->orderBy('razao_social')
                        ->limit(50)
                        ->pluck('razao_social', 'id')
                        ->toArray()
                    )
                    ->getOptionLabelUsing(fn ($value): ?string => Empresa::query()
                        ->whereKey($value)
                        ->value('razao_social')
                    )
                    ->searchable()
                    ->native(false)
                    ->required()
                    ->visible(fn () => Filament::auth()->user()?->isSuperAdmin()),

                Hidden::make('empresa_id')
                    ->default(fn () => Filament::auth()->user()?->empresa_id)
                    ->visible(fn () => ! Filament::auth()->user()?->isSuperAdmin()),

                TextInput::make('nome')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->rules(fn (?Responsavel $record): array => [
                        Rule::unique('users', 'email')->ignore($record?->user_id),
                    ])
                    ->maxLength(255),

                TextInput::make('telefone')
                    ->label('Telefone')
                    ->maxLength(50),

                TextInput::make('cargo')
                    ->label('Cargo')
                    ->maxLength(255),

                Select::make('perfil_acesso')
                    ->label('Tipo de acesso')
                    ->options([
                        'gestor' => 'Gestor',
                        'user' => 'Usuário',
                    ])
                    ->required()
                    ->native(false)
                    ->live()
                    ->dehydrated(false)
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->helperText('Escolha se esta pessoa será gestor ou usuário.'),

                Select::make('modo_senha')
                    ->label('Senha de acesso')
                    ->options([
                        'usuario_define' => 'Enviar link para o usuário criar a senha',
                        'admin_define' => 'Definir senha agora',
                    ])
                    ->default('usuario_define')
                    ->required()
                    ->native(false)
                    ->live()
                    ->dehydrated(false)
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->helperText('Para empresas grandes, o ideal é deixar o próprio usuário criar a senha pelo link enviado por e-mail.'),

                TextInput::make('senha_inicial')
                    ->label('Senha inicial')
                    ->password()
                    ->revealable()
                    ->minLength(6)
                    ->required(fn ($get, string $operation): bool => $operation === 'create' && $get('modo_senha') === 'admin_define')
                    ->dehydrated(false)
                    ->visible(fn ($get, string $operation): bool => $operation === 'create' && $get('modo_senha') === 'admin_define'),

                Select::make('user_id')
                    ->label('Usuário vinculado')
                    ->searchable()
                    ->native(false)
                    ->getSearchResultsUsing(function (string $search) use ($user): array {
                        $query = User::query()
                            ->select(['id', 'name'])
                            ->where('name', 'like', "%{$search}%")
                            ->orderBy('name')
                            ->limit(50);

                        if (! $user?->isSuperAdmin()) {
                            $query->where('empresa_id', $user?->empresa_id);
                        }

                        return $query->pluck('name', 'id')->toArray();
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => User::query()
                        ->whereKey($value)
                        ->value('name')
                    )
                    ->unique(Responsavel::class, 'user_id', ignoreRecord: true)
                    ->visible(fn () => Filament::auth()->user()?->isSuperAdmin())
                    ->helperText('Somente Super Admin usa esse campo.'),

                Select::make('gestor_user_id')
                    ->label('Gestor responsável (opcional)')
                    ->searchable()
                    ->native(false)
                    ->getSearchResultsUsing(function (string $search, $get) use ($user): array {
                        $empresaId = $user?->isSuperAdmin()
                            ? $get('empresa_id')
                            : $user?->empresa_id;

                        if (! $empresaId) {
                            return [];
                        }

                        return User::query()
                            ->select(['id', 'name'])
                            ->where('empresa_id', $empresaId)
                            ->where('role', 'gestor')
                            ->where('name', 'like', "%{$search}%")
                            ->orderBy('name')
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => User::query()
                        ->whereKey($value)
                        ->value('name')
                    )
                    ->required(false)
                    ->nullable()
                    ->helperText('Opcional: selecione um gestor se desejar vincular este usuário.'),
            ])
            ->columns(2);
    }
}