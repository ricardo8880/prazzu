<?php

namespace App\Filament\Resources\SugestaoMelhorias;

use App\Filament\Resources\SugestaoMelhorias\Pages\CreateSugestaoMelhoria;
use App\Filament\Resources\SugestaoMelhorias\Pages\EditSugestaoMelhoria;
use App\Filament\Resources\SugestaoMelhorias\Pages\ListSugestaoMelhorias;
use App\Filament\Resources\SugestaoMelhorias\Schemas\SugestaoMelhoriaForm;
use App\Filament\Resources\SugestaoMelhorias\Tables\SugestaoMelhoriasTable;
use App\Models\SugestaoMelhoria;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SugestaoMelhoriaResource extends Resource
{
    protected static ?string $model = SugestaoMelhoria::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-light-bulb';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    protected static ?string $navigationLabel = 'Relatar Bug / Melhoria';

    protected static ?string $modelLabel = 'Relato de Bug / Melhoria';

    protected static ?string $pluralModelLabel = 'Relatos de Bugs / Melhorias';

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return SugestaoMelhoriaForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return SugestaoMelhoriasTable::make($table);
    }

    public static function canCreate(): bool
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return false;
        }

        return ! $user->isSuperAdmin();
    }

    public static function canDelete($record): bool
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    public static function canDeleteAny(): bool
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        $query = parent::getEloquentQuery()
            ->with([
                'empresa:id,razao_social',
                'usuario:id,name,email,empresa_id,role',
                'analisador:id,name,email',
            ]);

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        return $query->visibleForUser($user);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSugestaoMelhorias::route('/'),
            'create' => CreateSugestaoMelhoria::route('/create'),
            'edit' => EditSugestaoMelhoria::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::auth()->check();
    }
}
