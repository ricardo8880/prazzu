<?php

namespace App\Support;

use App\Models\User;
use App\Services\PlanoService;
use Filament\Facades\Filament;

class PrazzuAccessControl
{
    public static function user(): ?User
    {
        $user = Filament::auth()->user() ?: auth()->user();

        return $user instanceof User ? $user : null;
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

        return (bool) $user->empresa?->isAtivo();
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
