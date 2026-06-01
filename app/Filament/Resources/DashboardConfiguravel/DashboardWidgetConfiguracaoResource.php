<?php

namespace App\Filament\Resources\DashboardConfiguravel;

use App\Filament\Resources\DashboardConfiguravel\Pages\CreateDashboardConfiguravel;
use App\Filament\Resources\DashboardConfiguravel\Pages\EditDashboardConfiguravel;
use App\Filament\Resources\DashboardConfiguravel\Pages\ListDashboardConfiguravel;
use App\Filament\Resources\DashboardConfiguravel\Pages\VisualizarDashboardConfiguravel;
use App\Models\DashboardWidgetConfiguracao;
use App\Models\Empresa;
use App\Services\PlanoService;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DashboardWidgetConfiguracaoResource extends Resource
{
    protected static ?string $model = DashboardWidgetConfiguracao::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Dashboard Configurável';

    protected static string|\UnitEnum|null $navigationGroup = 'Relatórios';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $user = Filament::auth()->user();

        return $schema->components([
            Section::make('Widget')
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

                    TextInput::make('titulo')
                        ->label('Título')
                        ->required()
                        ->maxLength(255),

                    Select::make('tipo')
                        ->label('Tipo')
                        ->required()
                        ->native(false)
                        ->default('card')
                        ->options([
                            'card' => 'Card',
                            'tabela' => 'Tabela',
                            'grafico' => 'Gráfico',
                        ]),

                    Select::make('fonte')
                        ->label('Indicador')
                        ->required()
                        ->native(false)
                        ->default('itens_abertos')
                        ->options([
                            'itens_abertos' => 'Itens abertos',
                            'itens_vencidos' => 'Itens vencidos',
                            'vencendo_hoje' => 'Vencendo hoje',
                            'aprovacoes_pendentes' => 'Aprovações pendentes',
                            'comentarios_atribuidos' => 'Comentários atribuídos',
                            'bloqueados' => 'Tarefas bloqueadas',
                            'valor_em_aberto' => 'Valor em aberto',
                            'pendente_cobranca' => 'Pendente de cobrança',
                            'carga_responsavel' => 'Carga por responsável',
                            'status_gargalo' => 'Gargalo por status',
                            'sla_vencido' => 'SLA vencido',
                            'contratos_ativos' => 'Contratos ativos',
                            'total_itens' => 'Total de itens',
                        ]),

                    Select::make('largura')
                        ->label('Largura')
                        ->required()
                        ->native(false)
                        ->default('1/3')
                        ->options([
                            '1/3' => 'Pequeno',
                            '1/2' => 'Médio',
                            '1/1' => 'Grande',
                        ]),

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
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordem')
            ->striped()
            ->columns([
                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('empresa.razao_social')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('fonte')
                    ->label('Indicador')
                    ->badge()
                    ->color('info'),

                TextColumn::make('largura')
                    ->label('Largura'),

                TextColumn::make('ordem')
                    ->label('Ordem')
                    ->sortable(),

                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->recordActions([
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
            ->visibleForUser(Filament::auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return true;
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
            'index' => VisualizarDashboardConfiguravel::route('/'),
            'visualizar' => VisualizarDashboardConfiguravel::route('/visualizar'),
            'gerenciar' => ListDashboardConfiguravel::route('/gerenciar'),
            'create' => CreateDashboardConfiguravel::route('/create'),
            'edit' => EditDashboardConfiguravel::route('/{record}/edit'),
        ];
    }
}
