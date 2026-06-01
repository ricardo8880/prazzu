<?php

namespace App\Filament\Resources\RelatoriosPersonalizados;

use App\Filament\Resources\RelatoriosPersonalizados\Pages\CreateRelatorioPersonalizado;
use App\Filament\Resources\RelatoriosPersonalizados\Pages\EditRelatorioPersonalizado;
use App\Filament\Resources\RelatoriosPersonalizados\Pages\ListRelatoriosPersonalizados;
use App\Filament\Resources\RelatoriosPersonalizados\Pages\VisualizarRelatoriosPersonalizados;
use App\Models\Empresa;
use App\Models\RelatorioPersonalizado;
use App\Services\PlanoService;
use App\Services\RelatorioPersonalizadoService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RelatorioPersonalizadoResource extends Resource
{
    protected static ?string $model = RelatorioPersonalizado::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Relatórios';

    protected static string|\UnitEnum|null $navigationGroup = 'Relatórios';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Relatórios';
    }

    public static function form(Schema $schema): Schema
    {
        $user = Filament::auth()->user();

        return $schema->components([
            Section::make('Configuração')
                ->description('Defina a base do relatório. Depois escolha as colunas e filtros que vão aparecer na visualização personalizada.')
                ->schema([
                    Select::make('empresa_id')
                        ->label('Empresa')
                        ->required()
                        ->native(false)
                        ->searchable()
                        ->default(fn () => $user?->isSuperAdmin() ? null : $user?->empresa_id)
                        ->disabled(fn () => $user?->isSuperAdmin() !== true)
                        ->dehydrated(true)
                        ->options(
                            fn () => $user?->isSuperAdmin()
                                ? Empresa::query()
                                    ->orderBy('razao_social')
                                    ->limit(100)
                                    ->pluck('razao_social', 'id')
                                    ->toArray()
                                : Empresa::query()
                                    ->whereKey($user?->empresa_id)
                                    ->pluck('razao_social', 'id')
                                    ->toArray()
                        ),

                    TextInput::make('nome')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),

                    Select::make('fonte')
                        ->label('Fonte')
                        ->required()
                        ->native(false)
                        ->default('item_controles')
                        ->options([
                            'item_controles' => 'Itens de controle',
                        ]),

                    Select::make('formato_padrao')
                        ->label('Formato padrão')
                        ->required()
                        ->native(false)
                        ->default('tela')
                        ->options([
                            'tela' => 'Tela personalizada',
                            'pdf' => 'PDF',
                            'excel' => 'Excel',
                            'csv' => 'CSV',
                        ]),

                    Select::make('publico')
                        ->label('Visibilidade')
                        ->required()
                        ->native(false)
                        ->default(1)
                        ->options([
                            1 => 'Toda empresa',
                            0 => 'Somente eu',
                        ]),

                    Select::make('ativo')
                        ->label('Status')
                        ->required()
                        ->native(false)
                        ->default(1)
                        ->options([
                            1 => 'Ativo',
                            0 => 'Inativo',
                        ]),

                    Textarea::make('descricao')
                        ->label('Descrição')
                        ->columnSpanFull()
                        ->maxLength(1000),
                ])
                ->columns(3),

            Section::make('Colunas do relatório')
                ->description('Escolha as informações que devem aparecer no card, na prévia e na tabela do relatório.')
                ->schema([
                    Repeater::make('colunas')
                        ->label('Colunas')
                        ->relationship('colunas')
                        ->schema([
                            Select::make('campo')
                                ->label('Campo')
                                ->required()
                                ->native(false)
                                ->searchable()
                                ->options(RelatorioPersonalizadoService::CAMPOS_ITEM_CONTROLE),

                            TextInput::make('rotulo')
                                ->label('Rótulo')
                                ->maxLength(255),

                            Select::make('tipo')
                                ->label('Tipo visual')
                                ->required()
                                ->native(false)
                                ->default('texto')
                                ->options(RelatorioPersonalizadoService::TIPOS_COLUNA),

                            TextInput::make('ordem')
                                ->label('Ordem')
                                ->numeric()
                                ->default(1),

                            Select::make('ativo')
                                ->label('Ativo')
                                ->required()
                                ->native(false)
                                ->default(1)
                                ->options([
                                    1 => 'Sim',
                                    0 => 'Não',
                                ]),
                        ])
                        ->columns(5)
                        ->defaultItems(0)
                        ->reorderable()
                        ->orderColumn('ordem')
                        ->addActionLabel('Adicionar coluna')
                        ->itemLabel(fn (array $state): ?string => $state['rotulo'] ?? (RelatorioPersonalizadoService::CAMPOS_ITEM_CONTROLE[$state['campo'] ?? ''] ?? 'Nova coluna')),
                ]),

            Section::make('Filtros fixos')
                ->description('Filtros opcionais que limitam automaticamente os dados do relatório. Deixe vazio para não filtrar.')
                ->schema([
                    Repeater::make('filtros')
                        ->label('Filtros')
                        ->relationship('filtros')
                        ->schema([
                            Select::make('campo')
                                ->label('Campo')
                                ->required()
                                ->native(false)
                                ->searchable()
                                ->options(RelatorioPersonalizadoService::CAMPOS_FILTRO_ITEM_CONTROLE),

                            Select::make('operador')
                                ->label('Operador')
                                ->required()
                                ->native(false)
                                ->default('igual')
                                ->options(RelatorioPersonalizadoService::OPERADORES),

                            TextInput::make('valor_padrao')
                                ->label('Valor')
                                ->maxLength(255),

                            TextInput::make('rotulo')
                                ->label('Rótulo')
                                ->maxLength(255),

                            TextInput::make('ordem')
                                ->label('Ordem')
                                ->numeric()
                                ->default(1),

                            Select::make('ativo')
                                ->label('Ativo')
                                ->required()
                                ->native(false)
                                ->default(1)
                                ->options([
                                    1 => 'Sim',
                                    0 => 'Não',
                                ]),

                            Select::make('obrigatorio')
                                ->label('Obrigatório')
                                ->required()
                                ->native(false)
                                ->default(0)
                                ->options([
                                    1 => 'Sim',
                                    0 => 'Não',
                                ]),
                        ])
                        ->columns(7)
                        ->defaultItems(0)
                        ->reorderable()
                        ->orderColumn('ordem')
                        ->addActionLabel('Adicionar filtro')
                        ->itemLabel(fn (array $state): ?string => $state['rotulo'] ?? (RelatorioPersonalizadoService::CAMPOS_FILTRO_ITEM_CONTROLE[$state['campo'] ?? ''] ?? 'Novo filtro')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('empresa.razao_social')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('fonte')
                    ->label('Fonte')
                    ->badge(),

                TextColumn::make('formato_padrao')
                    ->label('Formato')
                    ->badge(),

                TextColumn::make('colunas_count')
                    ->label('Colunas')
                    ->badge()
                    ->color('info'),

                TextColumn::make('filtros_count')
                    ->label('Filtros')
                    ->badge()
                    ->color('warning'),

                IconColumn::make('publico')
                    ->label('Público')
                    ->boolean(),

                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('visualizar')
                    ->label('Visualizar')
                    ->icon('heroicon-o-presentation-chart-bar')
                    ->color('success')
                    ->url(fn (): string => static::getUrl('index')),
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Excluir'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'empresa:id,razao_social',
            ])
            ->withCount([
                'colunas',
                'filtros',
            ])
            ->visibleForUser(Filament::auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return PlanoService::usuarioPossuiFeature(
            Filament::auth()->user(),
            PlanoService::FEATURE_RELATORIOS_PERSONALIZADOS
        );
    }

    public static function canCreate(): bool
    {
        return Filament::auth()->user()?->isAdmin() === true
            || Filament::auth()->user()?->isGestor() === true;
    }

    public static function canEdit(Model $record): bool
    {
        return static::canCreate();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canCreate();
    }

    public static function getPages(): array
    {
        return [
            'index' => VisualizarRelatoriosPersonalizados::route('/'),
            'gerenciar' => ListRelatoriosPersonalizados::route('/gerenciar'),
            'create' => CreateRelatorioPersonalizado::route('/create'),
            'edit' => EditRelatorioPersonalizado::route('/{record}/edit'),
        ];
    }
}
