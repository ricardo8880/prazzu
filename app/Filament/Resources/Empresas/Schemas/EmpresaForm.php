<?php

namespace App\Filament\Resources\Empresas\Schemas;

use App\Models\User;
use App\Services\PlanoService;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmpresaForm
{
    public static function make(Schema $schema): Schema
    {
        $user = Filament::auth()->user();

        return $schema
            ->components([
                Section::make('Dados da Empresa')
                    ->schema([
                        TextInput::make('razao_social')
                            ->label('Razão Social')
                            ->required()
                            ->maxLength(255)
                            ->trim(),

                        TextInput::make('nome_fantasia')
                            ->label('Nome Fantasia')
                            ->maxLength(255)
                            ->trim(),

                        TextInput::make('cnpj')
                            ->label('CNPJ')
                            ->maxLength(20)
                            ->trim()
                            ->unique('empresas', 'cnpj', ignoreRecord: true),

                        TextInput::make('email')
                            ->label('E-mail da Empresa')
                            ->email()
                            ->maxLength(255)
                            ->trim(),

                        TextInput::make('telefone')
                            ->label('Telefone')
                            ->maxLength(20)
                            ->trim(),

                        TextInput::make('responsavel_nome')
                            ->label('Responsável Comercial')
                            ->maxLength(255)
                            ->trim(),

                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('ativo')
                            ->options([
                                'pendente' => 'Pendente',
                                'ativo' => 'Ativo',
                                'inativo' => 'Inativo',
                            ])
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Plano e Limites')
                    ->description('Somente o super admin pode visualizar e alterar o plano e os limites comerciais da empresa.')
                    ->visible(fn (): bool => $user?->isSuperAdmin() === true)
                    ->schema([
                        Select::make('plano')
                            ->label('Plano')
                            ->required()
                            ->default(PlanoService::STARTER)
                            ->native(false)
                            ->live()
                            ->options(PlanoService::options())
                            ->afterStateUpdated(function ($state, callable $set): void {
                                $plano = PlanoService::normalizarPlano($state);

                                $set('plano', $plano);
                                $set('limite_usuarios', PlanoService::limiteUsuarios($plano));
                                $set('limite_itens', PlanoService::limiteItens($plano));
                                $set('limite_interacoes_ia', PlanoService::limiteInteracoesIa($plano));
                            })
                            ->helperText('Ao mudar o plano, os limites comerciais são preenchidos automaticamente conforme a tabela atual de planos.'),

                        TextInput::make('limite_usuarios')
                            ->label('Limite de Usuários')
                            ->numeric()
                            ->required()
                            ->default(3)
                            ->minValue(1),

                        TextInput::make('limite_itens')
                            ->label('Limite de Itens')
                            ->numeric()
                            ->required()
                            ->default(PlanoService::limiteItens(PlanoService::STARTER))
                            ->minValue(1),

                        TextInput::make('limite_interacoes_ia')
                            ->label('Interações IA/mês')
                            ->numeric()
                            ->required()
                            ->default(PlanoService::limiteInteracoesIa(PlanoService::STARTER))
                            ->minValue(0),

                        Toggle::make('ativo')
                            ->label('Empresa ativa')
                            ->default(true),
                    ])
                    ->columns(4),

                Section::make('Admin da Empresa')
                    ->description('Crie o primeiro usuário administrador da empresa. Depois ele poderá cadastrar os demais usuários.')
                    ->schema([
                        Toggle::make('criar_admin')
                            ->label('Criar admin da empresa agora')
                            ->default(true)
                            ->live()
                            ->dehydrated(true),

                        TextInput::make('admin_nome')
                            ->label('Nome do Admin')
                            ->maxLength(255)
                            ->trim()
                            ->required(fn (callable $get): bool => (bool) $get('criar_admin'))
                            ->visible(fn (callable $get): bool => (bool) $get('criar_admin'))
                            ->dehydrated(true),

                        TextInput::make('admin_email')
                            ->label('E-mail do Admin')
                            ->email()
                            ->maxLength(255)
                            ->trim()
                            ->required(fn (callable $get): bool => (bool) $get('criar_admin'))
                            ->visible(fn (callable $get): bool => (bool) $get('criar_admin'))
                            ->unique(User::class, 'email')
                            ->dehydrated(true),

                        TextInput::make('admin_password')
                            ->label('Senha inicial')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->maxLength(255)
                            ->required(fn (callable $get): bool => (bool) $get('criar_admin'))
                            ->visible(fn (callable $get): bool => (bool) $get('criar_admin'))
                            ->dehydrated(true),
                    ])
                    ->columns(2)
                    ->visible(
                        fn (?string $operation): bool =>
                            $operation === 'create'
                            && $user?->isSuperAdmin() === true
                    ),
            ]);
    }
}
