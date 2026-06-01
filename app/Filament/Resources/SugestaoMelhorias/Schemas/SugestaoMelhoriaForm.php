<?php

namespace App\Filament\Resources\SugestaoMelhorias\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SugestaoMelhoriaForm
{
    public static function make(Schema $schema): Schema
    {
        $user = Filament::auth()->user();
        $isSuperAdmin = $user?->isSuperAdmin() === true;

        return $schema
            ->components([
                Section::make('Sugestão')
                    ->description('Use este formulário para informar bugs, melhorias ou ideias para o sistema.')
                    ->schema([
                        Hidden::make('empresa_id')
                            ->default(fn (): ?int => $user?->empresa_id)
                            ->dehydrated(true),

                        Hidden::make('user_id')
                            ->default(fn (): ?int => $user?->id)
                            ->dehydrated(true),

                        Select::make('tipo')
                            ->label('Tipo')
                            ->required()
                            ->default('melhoria')
                            ->native(false)
                            ->options([
                                'bug' => 'Bug',
                                'melhoria' => 'Melhoria',
                                'funcionalidade' => 'Nova funcionalidade',
                                'duvida' => 'Dúvida',
                                'outro' => 'Outro',
                            ]),

                        Select::make('prioridade')
                            ->label('Prioridade')
                            ->required()
                            ->default('media')
                            ->native(false)
                            ->options([
                                'baixa' => 'Baixa',
                                'media' => 'Média',
                                'alta' => 'Alta',
                            ]),

                        TextInput::make('titulo')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->trim()
                            ->columnSpanFull(),

                        Textarea::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->rows(6)
                            ->maxLength(10000)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('aberta')
                            ->native(false)
                            ->visible(fn (): bool => Filament::auth()->user()?->isSuperAdmin() === true)
                            ->dehydrated(fn (): bool => Filament::auth()->user()?->isSuperAdmin() === true)
                            ->options([
                                'aberta' => 'Aberta',
                                'em_analise' => 'Em análise',
                                'aceita' => 'Aceita',
                                'recusada' => 'Recusada',
                                'implementada' => 'Implementada',
                            ]),

                        Textarea::make('resposta_admin')
                            ->label('Resposta do administrador')
                            ->rows(5)
                            ->placeholder('Resposta oficial do super administrador.')
                            ->visible(fn (): bool => Filament::auth()->user()?->isSuperAdmin() === true)
                            ->disabled(fn (): bool => Filament::auth()->user()?->isSuperAdmin() !== true)
                            ->dehydrated(fn (): bool => Filament::auth()->user()?->isSuperAdmin() === true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}