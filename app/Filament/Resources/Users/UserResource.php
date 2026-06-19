<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use App\Support\PrazzuAccessControl;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | UnitEnum | null $navigationGroup = 'Configurações';

    protected static ?string $navigationLabel = 'Usuários';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $pluralModelLabel = 'Usuários';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return UserForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::make($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        $query = parent::getEloquentQuery()
            ->select([
                'id',
                'name',
                'email',
                'role',
                'perfil_contabil',
                'empresa_id',
                'created_at',
                'updated_at',
            ])
            ->with(['empresa']);

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdminEmpresa()) {
            return $query->where('empresa_id', $user->empresa_id);
        }

        return $query->whereKey($user->id);
    }

    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();

        return PrazzuAccessControl::can('governanca.view', $user)
            && ($user?->isSuperAdmin() === true || $user?->isAdminEmpresa() === true);
    }

    public static function canCreate(): bool
    {
        $user = Filament::auth()->user();

        if (! $user || ! PrazzuAccessControl::can('governanca.create', $user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->isAdminEmpresa()) {
            return false;
        }

        $empresa = $user->empresa;

        if (! $empresa || ! $empresa->isAtivo()) {
            return false;
        }

        if ($empresa->atingiuLimiteUsuarios()) {
            return false;
        }

        return true;
    }

    public static function canEdit(Model $record): bool
    {
        $user = Filament::auth()->user();

        if (! $user || ! PrazzuAccessControl::can('governanca.edit', $user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdminEmpresa()
            && (int) $user->empresa_id === (int) $record->empresa_id
            && $record->role !== 'super_admin';
    }

    public static function canDelete(Model $record): bool
    {
        $user = Filament::auth()->user();

        if (! $user || ! PrazzuAccessControl::can('governanca.delete', $user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($record->role === 'admin' || $record->role === 'super_admin') {
            return false;
        }

        return $user->isAdminEmpresa()
            && (int) $user->empresa_id === (int) $record->empresa_id;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
