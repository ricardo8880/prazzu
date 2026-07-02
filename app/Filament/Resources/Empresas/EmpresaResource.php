<?php

namespace App\Filament\Resources\Empresas;

use App\Filament\Resources\Empresas\Pages\CreateEmpresa;
use App\Filament\Resources\Empresas\Pages\EditEmpresa;
use App\Filament\Resources\Empresas\Pages\ListEmpresas;
use App\Filament\Resources\Empresas\Schemas\EmpresaForm;
use App\Filament\Resources\Empresas\Tables\EmpresasTable;
use App\Models\Empresa;
use App\Support\PrazzuAccessControl;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmpresaResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Empresas';

    protected static ?string $modelLabel = 'Empresa';

    protected static ?string $pluralModelLabel = 'Empresas';

    protected static string|\UnitEnum|null $navigationGroup = 'Administração';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return EmpresaForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return EmpresasTable::make($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        $query = parent::getEloquentQuery()
            ->select([
                'id',
                'razao_social',
                'nome_fantasia',
                'cnpj',
                'email',
                'telefone',
                'responsavel_nome',
                'status',
                'plano',
                'limite_usuarios',
                'limite_itens',
                'limite_interacoes_ia',
                'ativo',
                'created_at',
                'updated_at',
            ])
            ->withCount([
                'users',
                'responsaveis',
                'itemControles',
            ])
            ->with(['assinaturaAtual']);

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdminEmpresa()) {
            return $query->whereKey($user->empresa_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();

        return PrazzuAccessControl::can('clientes.view', $user)
            && ($user?->isSuperAdmin() === true || $user?->isAdminEmpresa() === true);
    }

    public static function canCreate(): bool
    {
        return PrazzuAccessControl::can('clientes.create') && Filament::auth()->user()?->isSuperAdmin() === true;
    }

    public static function canEdit($record): bool
    {
        $user = Filament::auth()->user();

        if (! $user || ! $record || ! PrazzuAccessControl::can('clientes.edit', $user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdminEmpresa() && (int) $user->empresa_id === (int) $record->id;
    }

    public static function canDelete($record): bool
    {
        return PrazzuAccessControl::can('clientes.delete') && Filament::auth()->user()?->isSuperAdmin() === true;
    }

    public static function canDeleteAny(): bool
    {
        return PrazzuAccessControl::can('clientes.delete') && Filament::auth()->user()?->isSuperAdmin() === true;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmpresas::route('/'),
            'create' => CreateEmpresa::route('/create'),
            'edit' => EditEmpresa::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
