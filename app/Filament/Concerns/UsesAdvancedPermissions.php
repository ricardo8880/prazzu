<?php

namespace App\Filament\Concerns;

use App\Models\User;
use App\Services\PrazzuPermissionService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

trait UsesAdvancedPermissions
{
    protected static function advancedPermissionUser(): ?User
    {
        $user = Filament::auth()->user() ?: auth()->user();

        return $user instanceof User ? $user : null;
    }

    protected static function canAdvancedPermission(string $permission, string $scope = 'empresa'): bool
    {
        return app(PrazzuPermissionService::class)->can(static::advancedPermissionUser(), $permission, $scope);
    }

    protected function canDo(string $permission, string $scope = 'empresa'): bool
    {
        return app(PrazzuPermissionService::class)->can(static::advancedPermissionUser(), $permission, $scope);
    }

    protected function ensureCanDo(string $permission, ?string $message = null, string $scope = 'empresa'): bool
    {
        if ($this->canDo($permission, $scope)) {
            return true;
        }

        Notification::make()
            ->title('Acesso negado')
            ->body($message ?: 'Seu perfil não possui permissão para executar esta ação.')
            ->danger()
            ->send();

        return false;
    }

    protected function permissionFlags(string $module): array
    {
        $service = app(PrazzuPermissionService::class);
        $user = static::advancedPermissionUser();

        return collect(array_keys(PrazzuPermissionService::ACTIONS))
            ->mapWithKeys(fn (string $action): array => [$action => $service->can($user, $module . '.' . $action)])
            ->all();
    }
}
