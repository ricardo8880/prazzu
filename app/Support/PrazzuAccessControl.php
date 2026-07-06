<?php

namespace App\Support;

use App\Models\User;
use App\Services\PrazzuPermissionService;
use App\Services\PlanoService;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class PrazzuAccessControl
{
    public static function user(): ?User
    {
        $user = Filament::auth()->user() ?: auth()->user();

        return $user instanceof User ? $user : null;
    }


    public static function can(string $permission, ?User $user = null, string $scope = 'empresa'): bool
    {
        $user ??= self::user();

        return app(PrazzuPermissionService::class)->can($user, $permission, $scope);
    }

    public static function canAny(array $permissions, ?User $user = null, string $scope = 'empresa'): bool
    {
        $user ??= self::user();

        return app(PrazzuPermissionService::class)->canAny($user, $permissions, $scope);
    }

    public static function canAccessAdminPanel(?User $user = null): bool
    {
        $user ??= self::user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return self::canUseWorkArea($user);
    }

    public static function canUseWorkArea(?User $user = null): bool
    {
        $user ??= self::user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasEmpresaVinculada()) {
            return false;
        }

        return (bool) $user->empresa?->isAtivo();
    }

    public static function userCanAccessEmpresa(int|string|null $empresaId, ?User $user = null): bool
    {
        $user ??= self::user();

        if (! $user || blank($empresaId)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return (int) $user->empresa_id === (int) $empresaId;
    }

    public static function applyEmpresaScope(Builder|QueryBuilder $query, ?User $user = null, string $column = 'empresa_id'): Builder|QueryBuilder
    {
        $user ??= self::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if (! $user->hasEmpresaVinculada()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($column, $user->empresa_id);
    }

    public static function canUseFeature(string $feature, ?User $user = null): bool
    {
        $user ??= self::user();

        if (! self::canUseWorkArea($user)) {
            return false;
        }

        return PlanoService::usuarioPossuiFeature($user, $feature);
    }

    public static function canUseChecklist(?User $user = null): bool
    {
        return self::canUseFeature(PlanoService::FEATURE_CHECKLIST, $user);
    }

    public static function canUseTimeline(?User $user = null): bool
    {
        return self::canUseFeature(PlanoService::FEATURE_TIMELINE, $user);
    }

    public static function canUseComentarios(?User $user = null): bool
    {
        return self::canUseFeature(PlanoService::FEATURE_COMENTARIOS, $user);
    }

    public static function canUseAnexos(?User $user = null): bool
    {
        return self::canUseFeature(PlanoService::FEATURE_MULTIPLOS_ANEXOS, $user);
    }

    public static function canUseAprovacoes(?User $user = null): bool
    {
        return self::canUseFeature(PlanoService::FEATURE_APROVACOES, $user);
    }

    public static function canUseContratos(?User $user = null): bool
    {
        return self::canUseFeature(PlanoService::FEATURE_CONTRATOS, $user);
    }

    public static function canUseDashboardOperacional(?User $user = null): bool
    {
        return self::canUseFeature(PlanoService::FEATURE_DASHBOARD_OPERACIONAL, $user);
    }

    public static function canUsePortalCliente(?User $user = null): bool
    {
        return self::canUseFeature(PlanoService::FEATURE_PORTAL_CLIENTE, $user);
    }
}
