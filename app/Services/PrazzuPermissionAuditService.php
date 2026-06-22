<?php

namespace App\Services;

use App\Models\PrazzuPermissionAudit;
use App\Models\PrazzuPermission;
use App\Models\PrazzuRole;
use App\Models\PrazzuUserPermission;
use App\Models\PrazzuUserRole;
use App\Models\User;
use App\Support\CachedSchema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PrazzuPermissionAuditService
{
    public function record(string $event, array $payload = []): void
    {
        if (! CachedSchema::hasTable('prazzu_permission_audits')) {
            return;
        }

        PrazzuPermissionAudit::query()->create([
            'actor_user_id' => $payload['actor_user_id'] ?? Auth::id(),
            'target_user_id' => $payload['target_user_id'] ?? null,
            'role_id' => $payload['role_id'] ?? null,
            'event' => $event,
            'module' => $payload['module'] ?? null,
            'action' => $payload['action'] ?? null,
            'scope' => $payload['scope'] ?? null,
            'allowed' => array_key_exists('allowed', $payload) ? (bool) $payload['allowed'] : null,
            'reason' => $payload['reason'] ?? null,
            'before_payload' => $payload['before_payload'] ?? null,
            'after_payload' => $payload['after_payload'] ?? null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public function recent(int $limit = 30): Collection
    {
        if (! CachedSchema::hasTable('prazzu_permission_audits')) {
            return collect();
        }

        return PrazzuPermissionAudit::query()
            ->with(['actor', 'targetUser', 'role'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function userEffectivePermissions(?User $user, PrazzuPermissionService $permissionService): array
    {
        if (! $user) {
            return [];
        }

        $rows = [];

        foreach ($permissionService->defaultPermissionMatrix() as $module => $actions) {
            foreach ($actions as $action) {
                foreach (array_keys(PrazzuPermissionService::SCOPES) as $scope) {
                    $rows[] = $permissionService->effectivePermissionDetails($user, $module, $action, $scope);
                }
            }
        }

        return $rows;
    }

    public function rolePermissionSnapshot(PrazzuRole $role): array
    {
        if (! CachedSchema::hasTable('prazzu_permissions')) {
            return [];
        }

        return PrazzuPermission::query()
            ->where('role_id', $role->id)
            ->orderBy('module')
            ->orderBy('action')
            ->get(['module', 'action', 'scope'])
            ->map(fn (PrazzuPermission $permission) => $permission->module . '.' . $permission->action . ':' . $permission->scope)
            ->values()
            ->all();
    }
}
