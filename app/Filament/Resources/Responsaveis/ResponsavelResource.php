<?php

namespace App\Filament\Resources\Responsaveis;

use App\Filament\Pages\Clientes;
use App\Filament\Resources\Responsaveis\Pages\CreateResponsavel;
use App\Filament\Resources\Responsaveis\Pages\EditResponsavel;
use App\Filament\Resources\Responsaveis\Pages\ListResponsaveis;
use App\Filament\Resources\Responsaveis\Schemas\ResponsavelForm;
use App\Filament\Resources\Responsaveis\Tables\ResponsaveisTable;
use App\Models\Responsavel;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResponsavelResource extends Resource
{
    protected static ?string $model = Responsavel::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Responsáveis';

    protected static ?string $modelLabel = 'Responsável';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static string|\UnitEnum|null $navigationGroup = 'Cadastros e Configurações';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return ResponsavelForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return ResponsaveisTable::make($table);
    }

    public static function getNavigationUrl(): string
    {
        return Clientes::getUrl();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->select([
                'id',
                'nome',
                'email',
                'telefone',
                'cargo',
                'empresa_id',
                'user_id',
                'gestor_user_id',
                'created_at',
            ])
            ->with([
                'user:id,name',
                'gestor:id,name',
            ])
            ->withCount('itemControles');

        $user = Filament::auth()->user();

        if ($user?->isSuperAdmin()) {
            return $query;
        }

        return $query->where('empresa_id', $user?->empresa_id);
    }

    public static function canViewAny(): bool
    {
        return Filament::auth()->user()?->isAdmin() === true;
    }

    public static function canCreate(): bool
    {
        return Filament::auth()->user()?->isAdmin() === true;
    }

    public static function canEdit($record): bool
    {
        return Filament::auth()->user()?->isAdmin() === true;
    }

    public static function canDelete($record): bool
    {
        return Filament::auth()->user()?->isAdmin() === true;
    }

    public static function canDeleteAny(): bool
    {
        return Filament::auth()->user()?->isAdmin() === true;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResponsaveis::route('/'),
            'create' => CreateResponsavel::route('/create'),
            'edit' => EditResponsavel::route('/{record}/edit'),
        ];
    }
}
