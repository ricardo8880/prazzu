<?php

namespace App\Filament\Resources;

use App\Models\CategoriaItemControle;
use App\Models\CategoriaItemControleChecklistTemplate;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriaChecklistTemplateResource extends Resource
{
    protected static ?string $model = CategoriaItemControleChecklistTemplate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Checklist';

    protected static string|\UnitEnum|null $navigationGroup = 'TRABALHO';

    protected static ?int $navigationSort = 6;

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        return parent::getEloquentQuery()
            ->select([
                'id',
                'categoria_item_controle_id',
                'titulo',
                'ordem',
                'ativo',
                'created_at',
                'updated_at',
            ])
            ->with([
                'categoria:id,empresa_id,nome,cor,ativo',
                'categoria.empresa:id,razao_social',
            ])
            ->when(! $user?->isSuperAdmin(), function (Builder $query) use ($user): void {
                $query->whereHas('categoria', function (Builder $categoriaQuery) use ($user): void {
                    $categoriaQuery->where('empresa_id', $user?->empresa_id);
                });
            });
    }

    public static function form(Schema $schema): Schema
    {
        $user = Filament::auth()->user();

        return $schema
            ->components([
                Select::make('categoria_item_controle_id')
                    ->label('Categoria')
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->getSearchResultsUsing(function (string $search) use ($user): array {
                        return CategoriaItemControle::query()
                            ->visibleForUser($user)
                            ->where('ativo', true)
                            ->where('nome', 'like', "%{$search}%")
                            ->orderBy('nome')
                            ->limit(50)
                            ->pluck('nome', 'id')
                            ->toArray();
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => blank($value)
                        ? null
                        : CategoriaItemControle::query()->whereKey($value)->value('nome')
                    ),

                TextInput::make('titulo')
                    ->label('Etapa')
                    ->required()
                    ->maxLength(255)
                    ->trim(),

                TextInput::make('ordem')
                    ->label('Ordem')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                Toggle::make('ativo')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordem')
            ->columns([
                TextColumn::make('categoria.nome')
                    ->label('Categoria')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('categoria.empresa.razao_social')
                    ->label('Empresa')
                    ->toggleable(),

                TextColumn::make('titulo')
                    ->label('Etapa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ordem')
                    ->label('Ordem')
                    ->sortable(),

                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
            ]);
    }
}
