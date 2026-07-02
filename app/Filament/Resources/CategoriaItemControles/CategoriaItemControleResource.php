<?php

namespace App\Filament\Resources\CategoriaItemControles;

use App\Filament\Resources\CategoriaItemControles\Pages\CreateCategoriaItemControle;
use App\Filament\Resources\CategoriaItemControles\Pages\EditCategoriaItemControle;
use App\Filament\Resources\CategoriaItemControles\Pages\ListCategoriaItemControles;
use App\Models\CategoriaItemControle;
use App\Models\Empresa;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CategoriaItemControleResource extends Resource
{
    protected static ?string $model = CategoriaItemControle::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Categorias Operacionais';

    protected static ?string $modelLabel = 'Categoria';

    protected static ?string $pluralModelLabel = 'Categorias';

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        $user = Filament::auth()->user();

        return $schema
            ->components([
                Section::make('Dados da Categoria')
                    ->schema([
                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->default(fn (): ?int => $user?->isSuperAdmin() ? null : $user?->empresa_id)
                            ->disabled(fn (): bool => $user?->isSuperAdmin() !== true)
                            ->dehydrated(true)
                            ->getSearchResultsUsing(fn (string $search): array => self::getEmpresaSearchResults($search, $user))
                            ->getOptionLabelUsing(fn ($value): ?string => blank($value)
                                ? null
                                : Empresa::query()->whereKey($value)->value('razao_social')
                            ),

                        TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->trim(),

                        Select::make('cor')
                            ->label('Cor')
                            ->native(false)
                            ->default('gray')
                            ->options([
                                'gray' => 'Cinza',
                                'info' => 'Azul',
                                'success' => 'Verde',
                                'warning' => 'Amarelo',
                                'danger' => 'Vermelho',
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
                    ])
                    ->columns(2),

                Section::make('Template de Checklist')
                    ->description('Cadastre etapas padrão. Ao criar um item com esta categoria, essas etapas podem ser usadas como base.')
                    ->schema([
                        Repeater::make('checklistTemplates')
                            ->label('Etapas padrão')
                            ->relationship('checklistTemplates')
                            ->schema([
                                TextInput::make('titulo')
                                    ->label('Etapa')
                                    ->required()
                                    ->maxLength(255)
                                    ->trim()
                                    ->columnSpan(8),

                                TextInput::make('ordem')
                                    ->label('Ordem')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->columnSpan(2),

                                Toggle::make('ativo')
                                    ->label('Ativo')
                                    ->default(true)
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->defaultItems(0)
                            ->addActionLabel('Adicionar etapa padrão')
                            ->reorderable()
                            ->orderColumn('ordem')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['titulo'] ?? 'Nova etapa padrão'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nome')
            ->striped()
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record): string => $record?->cor ?: 'gray'),

                TextColumn::make('empresa.razao_social')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('checklist_templates_count')
                    ->label('Templates')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray'),

                ActionGroup::make([
                    DeleteAction::make()
                        ->label('Excluir')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ])
                    ->label('Mais')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        return parent::getEloquentQuery()
            ->select([
                'id',
                'empresa_id',
                'nome',
                'cor',
                'ativo',
                'created_at',
                'updated_at',
            ])
            ->with([
                'empresa:id,razao_social',
            ])
            ->withCount([
                'checklistTemplates',
            ])
            ->visibleForUser($user);
    }

    public static function canViewAny(): bool
    {
        return Filament::auth()->check();
    }

    public static function canCreate(): bool
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->isAdminEmpresa()
            || $user->isGestor();
    }

    public static function canEdit(Model $record): bool
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return (int) $record->empresa_id === (int) $user->empresa_id
            && ($user->isAdminEmpresa() || $user->isGestor());
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function getEmpresaSearchResults(string $search, $user): array
    {
        if (! $user) {
            return [];
        }

        $query = Empresa::query()
            ->select(['id', 'razao_social'])
            ->where(function ($builder) use ($search): void {
                $builder->where('razao_social', 'like', "%{$search}%")
                    ->orWhere('nome_fantasia', 'like', "%{$search}%")
                    ->orWhere('cnpj', 'like', "%{$search}%");
            });

        if ($user->isSuperAdmin()) {
            return $query
                ->orderBy('razao_social')
                ->limit(50)
                ->pluck('razao_social', 'id')
                ->toArray();
        }

        if (! $user->hasEmpresaVinculada()) {
            return [];
        }

        return $query
            ->whereKey($user->empresa_id)
            ->limit(1)
            ->pluck('razao_social', 'id')
            ->toArray();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategoriaItemControles::route('/'),
            'create' => CreateCategoriaItemControle::route('/create'),
            'edit' => EditCategoriaItemControle::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
