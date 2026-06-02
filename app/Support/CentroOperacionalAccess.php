<?php

namespace App\Support;

use App\Models\ItemControle;
use App\Models\User;

class CentroOperacionalAccess
{
    public const ACTION_VIEW = 'view';
    public const ACTION_EXECUTE = 'execute';
    public const ACTION_APPROVE = 'approve';
    public const ACTION_CORRECT = 'correct';
    public const ACTION_FINANCIAL = 'financial';
    public const ACTION_DELEGATE = 'delegate';

    public static function canView(?User $user, ItemControle $item): bool
    {
        return $item->canBeAccessedBy($user);
    }

    public static function canExecute(?User $user, ItemControle $item): bool
    {
        return $item->canBeModifiedBy($user);
    }

    public static function canApprove(?User $user, ItemControle $item): bool
    {
        return $item->canBeApprovedBy($user);
    }

    public static function canCorrect(?User $user, ItemControle $item): bool
    {
        return self::canExecute($user, $item);
    }

    public static function canManageFinancial(?User $user, ItemControle $item): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasEmpresaVinculada()) {
            return false;
        }

        if ((int) $user->empresa_id !== (int) $item->empresa_id) {
            return false;
        }

        return $user->isAdminEmpresa() || $user->isGestor();
    }

    public static function canDelegate(?User $user, ItemControle $item): bool
    {
        return self::canManageFinancial($user, $item);
    }

    public static function can(?User $user, ItemControle $item, string $action): bool
    {
        return match ($action) {
            self::ACTION_VIEW => self::canView($user, $item),
            self::ACTION_EXECUTE => self::canExecute($user, $item),
            self::ACTION_APPROVE => self::canApprove($user, $item),
            self::ACTION_CORRECT => self::canCorrect($user, $item),
            self::ACTION_FINANCIAL => self::canManageFinancial($user, $item),
            self::ACTION_DELEGATE => self::canDelegate($user, $item),
            default => false,
        };
    }

    public static function actionLabel(string $action): string
    {
        return match ($action) {
            self::ACTION_VIEW => 'visualizar',
            self::ACTION_EXECUTE => 'executar',
            self::ACTION_APPROVE => 'aprovar',
            self::ACTION_CORRECT => 'solicitar correção',
            self::ACTION_FINANCIAL => 'atualizar financeiro',
            self::ACTION_DELEGATE => 'delegar',
            default => 'executar esta ação',
        };
    }

    public static function actionPermissions(?User $user, ItemControle $item): array
    {
        return [
            'view' => self::canView($user, $item),
            'execute' => self::canExecute($user, $item),
            'approve' => self::canApprove($user, $item),
            'correct' => self::canCorrect($user, $item),
            'financial' => self::canManageFinancial($user, $item),
            'delegate' => self::canDelegate($user, $item),
        ];
    }
}
