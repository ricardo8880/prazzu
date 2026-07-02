<?php

namespace App\Filament\Resources\FluxosOperacionais;

use App\Filament\Resources\FluxosOperacionais\Pages\CreateFluxoOperacional;
use App\Filament\Resources\FluxosOperacionais\Pages\EditFluxoOperacional;
use App\Filament\Resources\FluxosOperacionais\Pages\ListFluxosOperacionais;
use App\Filament\Resources\FluxosOperacionais\Pages\VerFluxoOperacional;
use App\Models\Empresa;
use App\Models\FluxoOperacional;
use App\Models\Responsavel;
use App\Services\PlanoService;
use App\Support\PrazzuAccessControl;
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

class FluxoOperacionalResource extends Resource
{
    protected static ?string $model = FluxoOperacional::class;
    protected static ?string $slug = 'fluxos-operacionais';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Fluxos Operacionais';

    protected static string|\UnitEnum|null $navigationGroup = 'Operação';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        $user = Filament::auth()->user();

        return $schema->components([
            Section::make('Dados do fluxo')
                ->description('Defina para qual empresa e tipo de item esse fluxo será usado. Depois cadastre as etapas que o cliente irá acompanhar.')
                ->schema([
                    Select::make('empresa_id')
                        ->label('Empresa')
                        ->required()
                        ->native(false)
                        ->searchable()
                        ->default(fn () => $user?->isSuperAdmin() ? null : $user?->empresa_id)
                        ->disabled(fn () => $user?->isSuperAdmin() !== true)
                        ->dehydrated(true)
                        ->options(fn () => $user?->isSuperAdmin()
                            ? Empresa::query()->orderBy('razao_social')->limit(100)->pluck('razao_social', 'id')->toArray()
                            : Empresa::query()->whereKey($user?->empresa_id)->pluck('razao_social', 'id')->toArray()),

                    TextInput::make('nome')
                        ->label('Nome do fluxo')
                        ->placeholder('Ex: Aprovação de contrato com assinatura')
                        ->required()
                        ->maxLength(255),

                    Select::make('tipo_item')
                        ->label('Tipo de item')
                        ->native(false)
                        ->default('todos')
                        ->options([
                            'todos' => 'Todos',
                            'contrato' => 'Contrato',
                            'documento' => 'Documento',
                            'licenca' => 'Licença',
                            'acordo' => 'Acordo',
                        ]),

                    Select::make('padrao')
                        ->label('Fluxo padrão')
                        ->helperText('Use “Sim” quando esse fluxo deve ser sugerido automaticamente para o tipo escolhido.')
                        ->native(false)
                        ->default(0)
                        ->options([1 => 'Sim', 0 => 'Não']),

                    Select::make('ativo')
                        ->label('Ativo')
                        ->helperText('Fluxos inativos ficam salvos, mas não devem ser usados em novos itens.')
                        ->native(false)
                        ->default(1)
                        ->options([1 => 'Sim', 0 => 'Não']),

                    Textarea::make('descricao')
                        ->label('Descrição')
                        ->placeholder('Explique o objetivo do fluxo para facilitar o entendimento do cliente.')
                        ->rows(4)
                        ->columnSpanFull()
                        ->maxLength(1000),
                ])
                ->columns([
                    'default' => 1,
                    'md' => 2,
                    'xl' => 3,
                ]),

            Section::make('Etapas do fluxo')
                ->description('Cadastre a sequência operacional. Cada etapa aparece depois na página visual do fluxo.')
                ->schema([
                    Repeater::make('etapas')
                        ->label('Etapas cadastradas')
                        ->relationship('etapas')
                        ->schema([
                            TextInput::make('nome')
                                ->label('Nome da etapa')
                                ->placeholder('Ex: Conferência dos dados')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('ordem')
                                ->label('Ordem')
                                ->numeric()
                                ->default(1),

                            TextInput::make('prazo_horas')
                                ->label('Prazo em horas')
                                ->numeric()
                                ->placeholder('Ex: 24'),

                            Select::make('responsavel_padrao_id')
                                ->label('Responsável padrão')
                                ->native(false)
                                ->searchable()
                                ->preload()
                                ->options(fn () => Responsavel::query()
                                    ->when(! $user?->isSuperAdmin(), fn (Builder $query) => $query->where('empresa_id', $user?->empresa_id))
                                    ->orderBy('nome')
                                    ->limit(100)
                                    ->pluck('nome', 'id')
                                    ->toArray()),

                            Select::make('exige_aprovacao')
                                ->label('Exige aprovação')
                                ->native(false)
                                ->default(0)
                                ->options([1 => 'Sim', 0 => 'Não']),

                            Select::make('ativo')
                                ->label('Ativo')
                                ->native(false)
                                ->default(1)
                                ->options([1 => 'Sim', 0 => 'Não']),

                            Textarea::make('descricao')
                                ->label('Descrição da etapa')
                                ->placeholder('Explique o que deve ser feito nesta etapa.')
                                ->rows(3)
                                ->columnSpanFull()
                                ->maxLength(1000),
                        ])
                        ->columns([
                            'default' => 1,
                            'lg' => 2,
                            '2xl' => 3,
                        ])
                        ->defaultItems(3)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['nome'] ?? 'Nova etapa')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nome')
            ->striped()
            ->columns([
                TextColumn::make('nome')->label('Nome')->searchable()->sortable(),
                TextColumn::make('empresa.razao_social')->label('Empresa')->searchable()->sortable()->toggleable(),
                TextColumn::make('tipo_item')->label('Tipo')->badge(),
                TextColumn::make('etapas_count')->label('Etapas')->badge()->color('info'),
                TextColumn::make('itens_count')->label('Itens')->badge()->color('gray'),
                IconColumn::make('padrao')->label('Padrão')->boolean(),
                IconColumn::make('ativo')->label('Ativo')->boolean(),
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Visualizar')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (FluxoOperacional $record): string => static::getUrl('ver', ['record' => $record->getKey()])),

                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Excluir'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['empresa:id,razao_social'])
            ->withCount(['etapas', 'itens'])
            ->visibleForUser(Filament::auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return PrazzuAccessControl::can('tarefas.view')
            && PlanoService::usuarioPossuiFeature(
                Filament::auth()->user(),
                PlanoService::FEATURE_FLUXOS_OPERACIONAIS
            );
    }

    public static function canCreate(): bool
    {
        return PrazzuAccessControl::can('tarefas.create')
            && (Filament::auth()->user()?->isAdmin() === true
                || Filament::auth()->user()?->isGestor() === true);
    }

    public static function canEdit(Model $record): bool
    {
        return PrazzuAccessControl::can('tarefas.edit') && static::canCreate();
    }

    public static function canDelete(Model $record): bool
    {
        return PrazzuAccessControl::can('tarefas.delete') && static::canCreate();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFluxosOperacionais::route('/'),
            'create' => CreateFluxoOperacional::route('/create'),
            'edit' => EditFluxoOperacional::route('/{record}/edit'),
            'ver' => VerFluxoOperacional::route('/{record}/ver'),
        ];
    }
}
