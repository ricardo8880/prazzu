<?php

namespace App\Services;

use App\Models\PrazzuPermission;
use App\Models\PrazzuPermissionRule;
use App\Models\PrazzuRole;
use App\Models\PrazzuUserPermission;
use App\Models\User;
use App\Support\CachedSchema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PrazzuPermissionService
{
    public const MODULES = [
        'clientes' => 'Clientes',
        'documentos' => 'Documentos',
        'cobrancas' => 'Cobranças',
        'financeiro' => 'Financeiro',
        'atendimentos' => 'Atendimentos',
        'tarefas' => 'Tarefas',
        'aprovacoes' => 'Aprovações',
        'armazenamento' => 'Armazenamento',
        'relatorios' => 'Relatórios',
        'governanca' => 'Governança',
    ];

    public const ACTIONS = [
        'view' => 'Ver',
        'create' => 'Criar',
        'edit' => 'Editar',
        'delete' => 'Excluir',
        'approve' => 'Aprovar',
        'cancel' => 'Cancelar',
        'reply' => 'Responder',
        'close' => 'Encerrar',
        'export' => 'Exportar',
        'reassign' => 'Reatribuir',
    ];

    public function can(?User $user, string $permission, string $scope = 'empresa'): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        [$module, $action] = $this->splitPermission($permission);

        if ($module === '' || $action === '') {
            return false;
        }

        $module = $this->normalize($module);
        $action = $this->normalizeAction($action);
        $scope = $this->normalizeScope($scope);

        if (! CachedSchema::hasTable('prazzu_permissions')) {
            return $this->fallbackCan($user, $module, $action);
        }

        $cacheKey = sprintf('prazzu_permission:%s:%s:%s:%s', $user->id, $module, $action, $scope);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $module, $action, $scope) {
            $direct = $this->directUserPermission($user, $module, $action, $scope);

            if ($direct !== null) {
                return $direct;
            }

            if ($this->hasRolePermission($user, $module, $action, $scope)) {
                return true;
            }

            if ($this->hasRulePermission($user, $module, $action)) {
                return true;
            }

            return $this->fallbackCan($user, $module, $action);
        });
    }


    public function canAny(?User $user, array $permissions, string $scope = 'empresa'): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($user, (string) $permission, $scope)) {
                return true;
            }
        }

        return false;
    }

    public function flushUserCache(User|int $user): void
    {
        $userId = $user instanceof User ? $user->id : $user;

        foreach (array_keys(self::MODULES) as $module) {
            foreach (array_keys(self::ACTIONS) as $action) {
                Cache::forget(sprintf('prazzu_permission:%s:%s:%s:%s', $userId, $module, $action, 'empresa'));
                Cache::forget(sprintf('prazzu_permission:%s:%s:%s:%s', $userId, $module, $action, 'all'));
            }
        }
    }

    public function defaultPermissionMatrix(): array
    {
        return [
            'clientes' => ['view', 'create', 'edit', 'delete', 'export'],
            'documentos' => ['view', 'create', 'edit', 'delete', 'approve', 'export'],
            'cobrancas' => ['view', 'create', 'edit', 'delete', 'approve', 'cancel'],
            'financeiro' => ['view', 'create', 'edit', 'delete', 'approve', 'export'],
            'atendimentos' => ['view', 'create', 'edit', 'reply', 'close', 'reassign'],
            'tarefas' => ['view', 'create', 'edit', 'delete', 'approve', 'reassign'],
            'aprovacoes' => ['view', 'approve'],
            'armazenamento' => ['view', 'create', 'edit', 'delete', 'export'],
            'relatorios' => ['view', 'export'],
            'governanca' => ['view', 'create', 'edit', 'delete', 'approve'],
        ];
    }

    private function splitPermission(string $permission): array
    {
        if (str_contains($permission, '.')) {
            return array_pad(explode('.', $permission, 2), 2, '');
        }

        if (str_contains($permission, ':')) {
            return array_pad(explode(':', $permission, 2), 2, '');
        }

        return ['', ''];
    }

    private function directUserPermission(User $user, string $module, string $action, string $scope): ?bool
    {
        if (! CachedSchema::hasTable('prazzu_user_permissions')) {
            return null;
        }

        $query = PrazzuUserPermission::query()
            ->where('user_id', $user->id)
            ->where('module', $module)
            ->where('action', $action)
            ->whereIn('scope', array_unique([$scope, 'all', 'empresa']))
            ->orderByRaw("FIELD(scope, ?, 'all', 'empresa')", [$scope]);

        $record = $query->first();

        return $record ? (bool) $record->allowed : null;
    }

    private function hasRolePermission(User $user, string $module, string $action, string $scope): bool
    {
        if (! CachedSchema::hasTable('prazzu_user_roles') || ! CachedSchema::hasTable('prazzu_roles')) {
            return false;
        }

        return PrazzuPermission::query()
            ->where('module', $module)
            ->where('action', $action)
            ->whereIn('scope', array_unique([$scope, 'all', 'empresa']))
            ->whereHas('role', fn ($query) => $query->where('active', true))
            ->whereHas('role.userRoles', fn ($query) => $query->where('user_id', $user->id))
            ->exists();
    }

    private function hasRulePermission(User $user, string $module, string $action): bool
    {
        if (! CachedSchema::hasTable('prazzu_permission_rules')) {
            return false;
        }

        $roles = collect([$user->role, $user->perfil_contabil])
            ->filter()
            ->map(fn ($role) => $this->normalizeRole((string) $role))
            ->unique()
            ->values();

        if ($roles->isEmpty()) {
            return false;
        }

        $column = match ($action) {
            'view' => 'can_view',
            'create' => 'can_create',
            'edit' => 'can_update',
            'delete' => 'can_delete',
            default => null,
        };

        if (! $column) {
            return false;
        }

        return PrazzuPermissionRule::query()
            ->whereIn('role', $roles->all())
            ->where('module', $module)
            ->where($column, true)
            ->exists();
    }

    private function fallbackCan(User $user, string $module, string $action): bool
    {
        if ($user->isAdmin() || $user->isGestor()) {
            return true;
        }

        if ($action === 'view') {
            return true;
        }

        if ($action === 'create' || $action === 'edit') {
            return in_array($module, ['tarefas', 'documentos', 'atendimentos'], true);
        }

        return false;
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->ascii()->lower()->replace([' ', '-'], '_')->squish()->toString();
    }

    private function normalizeRole(string $value): string
    {
        return $this->normalize($value);
    }

    private function normalizeAction(string $action): string
    {
        $action = $this->normalize($action);

        return match ($action) {
            'update', 'editar' => 'edit',
            'visualizar', 'ver' => 'view',
            'criar' => 'create',
            'excluir', 'destroy' => 'delete',
            'aprovar' => 'approve',
            'cancelar' => 'cancel',
            default => $action,
        };
    }

    private function normalizeScope(string $scope): string
    {
        return $this->normalize($scope ?: 'empresa');
    }
}
