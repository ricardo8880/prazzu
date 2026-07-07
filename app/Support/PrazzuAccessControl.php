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


    /**
     * Confirma se um registro pertence à mesma empresa do usuário autenticado.
     *
     * Objetivo do Lote 3: centralizar a regra multiempresa para evitar comparações
     * manuais divergentes espalhadas por Policies, Resources e Pages.
     */
    public static function canAccessCompanyRecord(object|array|null $record, ?User $user = null, string $companyKey = 'empresa_id'): bool
    {
        $user ??= self::user();

        if (! $user || ! $record) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasEmpresaVinculada()) {
            return false;
        }

        $empresaId = is_array($record)
            ? ($record[$companyKey] ?? null)
            : ($record->{$companyKey} ?? null);

        return filled($empresaId) && (int) $empresaId === (int) $user->empresa_id;
    }

    public static function abortUnlessCompanyRecord(object|array|null $record, ?User $user = null, string $companyKey = 'empresa_id'): void
    {
        abort_unless(self::canAccessCompanyRecord($record, $user, $companyKey), 403);
    }

    public static function requirePermission(string $permission, ?User $user = null, string $scope = 'empresa'): void
    {
        abort_unless(self::can($permission, $user, $scope), 403);
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


    /**
     * Gate central para paginas Filament internas.
     *
     * Evita o padrao inseguro auth()->check() em paginas sensiveis e garante:
     * - usuario autenticado;
     * - area de trabalho ativa/empresa vinculada para usuarios comuns;
     * - super_admin liberado;
     * - permissao funcional explicita para o modulo.
     */
    public static function canAccessPage(string|array $permissions, ?User $user = null, string $scope = 'empresa'): bool
    {
        $user ??= self::user();

        if (! self::canUseWorkArea($user)) {
            return false;
        }

        if ($user?->isSuperAdmin()) {
            return true;
        }

        return self::canAny((array) $permissions, $user, $scope);
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
