<?php

namespace App\Filament\Resources\Configuracoes;

use App\Filament\Resources\Configuracoes\Pages\EditConfiguracao;
use App\Filament\Resources\Configuracoes\Schemas\ConfiguracaoForm;
use App\Models\Configuracao;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ConfiguracaoResource extends Resource
{
    protected static ?string $model = Configuracao::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configurações';

    protected static ?string $modelLabel = 'Configuração';

    protected static ?string $pluralModelLabel = 'Configurações';

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ConfiguracaoForm::make($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        $query = parent::getEloquentQuery()
            ->with(['empresa:id,razao_social']);

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdminEmpresa()) {
            return $query->where('empresa_id', $user->empresa_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();

        return $user?->isSuperAdmin() === true || $user?->isAdminEmpresa() === true;
    }

    public static function canEdit($record): bool
    {
        $user = Filament::auth()->user();

        if (! $user || ! $record) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdminEmpresa()
            && (int) $record->empresa_id === (int) $user->empresa_id;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'edit' => EditConfiguracao::route('/'),
        ];
    }
}
