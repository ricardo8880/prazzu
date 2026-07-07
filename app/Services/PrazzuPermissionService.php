<?php

namespace App\Services;

use App\Models\PrazzuPermission;
use App\Models\PrazzuPermissionRule;
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
        'configuracoes' => 'Configurações',
        'auditoria' => 'Auditoria',
        'contratos' => 'Contratos',
        'system_health' => 'Saúde do Sistema',
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

    public const SCOPES = [
        'empresa' => 'Empresa',
        'proprio' => 'Próprio usuário',
        'equipe' => 'Equipe',
        'all' => 'Global',
    ];

    /**
     * Permissões legadas/técnicas que existem no banco e podem ser consultadas pelo backend,
     * mas não fazem parte da matriz editável da tela Perfis e Permissões.
     */
    public const UNMANAGED_PERMISSIONS = [
        'seguranca' => ['visibility'],
        'workflow' => ['manage_tags_status'],
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
        $module = $this->normalizeModule($module);
        $action = $this->normalizeAction($action);
        $scope = $this->normalizeScope($scope);

        if (! $this->isKnownPermission($module, $action) || ! $this->isValidScope($scope)) {
            return false;
        }

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

        foreach ($this->allKnownPermissionPairs() as [$module, $action]) {
            foreach (array_keys(self::SCOPES) as $scope) {
                Cache::forget(sprintf('prazzu_permission:%s:%s:%s:%s', $userId, $module, $action, $scope));
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
            'governanca' => ['view', 'create', 'edit', 'delete', 'approve', 'export'],
            'configuracoes' => ['view', 'edit'],
            'auditoria' => ['view', 'export'],
            'contratos' => ['view', 'create', 'edit', 'delete', 'approve', 'export'],
            'system_health' => ['view', 'export'],
        ];
    }

    public function matrixActionsForModule(string $module): array
    {
        return $this->defaultPermissionMatrix()[$this->normalizeModule($module)] ?? [];
    }

    public function isMatrixPermission(string $module, string $action): bool
    {
        $module = $this->normalizeModule($module);
        $action = $this->normalizeAction($action);

        return in_array($action, $this->defaultPermissionMatrix()[$module] ?? [], true);
    }

    public function isKnownPermission(string $module, string $action): bool
    {
        $module = $this->normalizeModule($module);
        $action = $this->normalizeAction($action);

        return $this->isMatrixPermission($module, $action)
            || in_array($action, self::UNMANAGED_PERMISSIONS[$module] ?? [], true);
    }

    public function isValidScope(string $scope): bool
    {
        return array_key_exists($this->normalizeScope($scope), self::SCOPES);
    }

    public function normalizeModule(string $module): string
    {
        return $this->normalize($module);
    }

    public function normalizeAction(string $action): string
    {
        $action = $this->normalize($action);

        return match ($action) {
            'update', 'editar' => 'edit',
            'visualizar', 'ver' => 'view',
            'criar' => 'create',
            'excluir', 'destroy' => 'delete',
            'aprovar' => 'approve',
            'cancelar' => 'cancel',
            'responder' => 'reply',
            'encerrar' => 'close',
            'exportar' => 'export',
            'reatribuir' => 'reassign',
            default => $action,
        };
    }

    public function normalizeScope(string $scope): string
    {
        $scope = $this->normalize($scope ?: 'empresa');

        return match ($scope) {
            'global', 'todos', 'all' => 'all',
            'proprio_usuario', 'proprio' => 'proprio',
            'time', 'equipe' => 'equipe',
            default => $scope ?: 'empresa',
        };
    }

    public function effectivePermissionDetails(User $user, string $module, string $action, string $scope = 'empresa'): array
    {
        $module = $this->normalizeModule($module);
        $action = $this->normalizeAction($action);
        $scope = $this->normalizeScope($scope);
        $roles = $this->roleNamesForUser($user);
        $override = $this->directUserPermissionRecord($user, $module, $action, $scope);
        $allowed = $this->can($user, $module . '.' . $action, $scope);

        if ($override) {
            $source = 'Exceção individual';
        } elseif ($this->hasRolePermission($user, $module, $action, $scope)) {
            $source = 'Perfil';
        } elseif ($this->hasRulePermission($user, $module, $action)) {
            $source = 'Regra antiga';
        } else {
            $source = 'Fallback';
        }

        return [
            'module' => $module,
            'action' => $action,
            'scope' => $scope,
            'allowed' => $allowed,
            'source' => $source,
            'roles' => $roles ?: '-',
            'override' => $override,
        ];
    }

    private function allKnownPermissionPairs(): array
    {
        $pairs = [];

        foreach ($this->defaultPermissionMatrix() + self::UNMANAGED_PERMISSIONS as $module => $actions) {
            foreach ($actions as $action) {
                $pairs[] = [$module, $action];
            }
        }

        return $pairs;
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
        $record = $this->directUserPermissionRecord($user, $module, $action, $scope);

        return $record ? (bool) $record->allowed : null;
    }

    private function directUserPermissionRecord(User $user, string $module, string $action, string $scope): ?PrazzuUserPermission
    {
        if (! CachedSchema::hasTable('prazzu_user_permissions')) {
            return null;
        }

        $scopes = $this->scopeFallbacks($scope);
        $orderSql = $this->scopeOrderSql($scopes);

        return PrazzuUserPermission::query()
            ->where('user_id', $user->id)
            ->where('module', $module)
            ->where('action', $action)
            ->whereIn('scope', $scopes)
            ->orderByRaw($orderSql)
            ->first();
    }

    private function hasRolePermission(User $user, string $module, string $action, string $scope): bool
    {
        if (! CachedSchema::hasTable('prazzu_user_roles') || ! CachedSchema::hasTable('prazzu_roles') || ! CachedSchema::hasTable('prazzu_permissions')) {
            return false;
        }

        return PrazzuPermission::query()
            ->where('module', $module)
            ->where('action', $action)
            ->whereIn('scope', $this->scopeFallbacks($scope))
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
        // Super admin já é liberado no início de can(). O fallback abaixo só existe para
        // instalações onde a matriz avançada ainda não foi carregada no banco.
        // Por segurança, módulos de governança/sistema nunca são liberados por fallback amplo.
        if ($module === 'system_health') {
            return false;
        }

        if ($user->isAdminEmpresa()) {
            return ! in_array($module, ['system_health'], true);
        }

        if ($user->isGestor()) {
            if (in_array($module, ['governanca', 'configuracoes', 'auditoria', 'system_health', 'financeiro'], true)) {
                return false;
            }

            return in_array($action, ['view', 'create', 'edit', 'reply', 'close', 'reassign', 'export'], true);
        }

        if ($action === 'view') {
            return ! in_array($module, ['governanca', 'configuracoes', 'auditoria', 'system_health', 'financeiro'], true);
        }

        if ($action === 'create' || $action === 'edit') {
            return in_array($module, ['tarefas', 'documentos', 'atendimentos'], true);
        }

        return false;
    }

    private function roleNamesForUser(User $user): string
    {
        if (! CachedSchema::hasTable('prazzu_user_roles')) {
            return '';
        }

        return \App\Models\PrazzuUserRole::query()
            ->with('role')
            ->where('user_id', $user->id)
            ->get()
            ->pluck('role.name')
            ->filter()
            ->implode(', ');
    }

    private function scopeFallbacks(string $scope): array
    {
        $scope = $this->normalizeScope($scope);

        return match ($scope) {
            'empresa' => ['empresa', 'all'],
            'all' => ['all', 'empresa'],
            default => [$scope, 'all', 'empresa'],
        };
    }

    private function scopeOrderSql(array $scopes): string
    {
        $cases = [];

        foreach (array_values($scopes) as $index => $scope) {
            $cases[] = "WHEN '" . str_replace("'", "''", $scope) . "' THEN " . $index;
        }

        return 'CASE scope ' . implode(' ', $cases) . ' ELSE 99 END';
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->ascii()->lower()->replace([' ', '-'], '_')->squish()->toString();
    }

    private function normalizeRole(string $value): string
    {
        return $this->normalize($value);
    }
}
