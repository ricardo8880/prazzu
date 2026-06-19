<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesAdvancedPermissions;

use App\Models\PrazzuPermission;
use App\Models\PrazzuPermissionRule;
use App\Models\PrazzuRole;
use App\Models\PrazzuUserPermission;
use App\Models\PrazzuUserRole;
use App\Models\User;
use App\Services\PrazzuPermissionAuditService;
use App\Services\PrazzuPermissionService;
use App\Support\CachedSchema;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use UnitEnum;

class Permissoes extends Page
{
    use UsesAdvancedPermissions;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';
    protected static string | UnitEnum | null $navigationGroup = 'Governança';
    protected static ?string $navigationLabel = 'Perfis e Permissões';
    protected static ?string $title = 'Perfis e Permissões';
    protected static ?int $navigationSort = 95;

    protected string $view = 'filament.pages.permissoes-management';


    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return static::canAdvancedPermission('governanca.view');
    }

    public string $roleName = '';
    public string $roleDescription = '';
    public bool $roleActive = true;
    public ?int $selectedRoleId = null;
    public ?int $selectedUserId = null;
    public ?int $assignRoleId = null;
    public string $overrideModule = 'clientes';
    public string $overrideAction = 'view';
    public string $overrideScope = 'empresa';
    public bool $overrideAllowed = true;
    public string $overrideReason = '';
    public array $rolePermissions = [];
    public string $auditFilter = 'all';

    public function mount(): void
    {
        $this->ensureTablesReady();
        $this->seedStarterRolesIfEmpty();

        $this->selectedRoleId = PrazzuRole::query()->orderBy('name')->value('id');
        $this->selectedUserId = User::query()->orderBy('name')->value('id');
        $this->assignRoleId = $this->selectedRoleId;
        $this->hydrateRolePermissions(app(PrazzuPermissionService::class));
    }

    public function updatedSelectedRoleId(): void
    {
        $this->assignRoleId = $this->selectedRoleId;
        $this->hydrateRolePermissions(app(PrazzuPermissionService::class));
    }

    public function createRole(): void
    {
        if (! $this->ensureCanDo('governanca.create')) {
            return;
        }

        $name = trim($this->roleName);

        if ($name === '') {
            Notification::make()->title('Informe o nome do perfil.')->warning()->send();
            return;
        }

        if (! CachedSchema::hasTable('prazzu_roles')) {
            Notification::make()->title('Tabela de perfis não encontrada. Execute o SQL/migration do lote 1.')->danger()->send();
            return;
        }

        $role = PrazzuRole::query()->updateOrCreate(
            ['name' => $name],
            [
                'description' => trim($this->roleDescription) ?: null,
                'active' => $this->roleActive,
            ]
        );

        app(PrazzuPermissionAuditService::class)->record('role.created', [
            'role_id' => $role->id,
            'reason' => 'Perfil criado ou atualizado pela tela Perfis e Permissões.',
            'after_payload' => $role->only(['name', 'description', 'active']),
        ]);

        $this->roleName = '';
        $this->roleDescription = '';
        $this->roleActive = true;
        $this->selectedRoleId = (int) $role->id;
        $this->assignRoleId = (int) $role->id;
        $this->hydrateRolePermissions(app(PrazzuPermissionService::class));
        $this->forgetPermissionCache();

        Notification::make()->title('Perfil salvo. Agora configure a matriz de permissões.')->success()->send();
    }

    public function saveRolePermissions(): void
    {
        if (! $this->ensureCanDo('governanca.edit')) {
            return;
        }

        $permissionService = app(PrazzuPermissionService::class);
        $auditService = app(PrazzuPermissionAuditService::class);
        if (! $this->selectedRoleId || ! CachedSchema::hasTable('prazzu_permissions')) {
            Notification::make()->title('Selecione um perfil válido.')->warning()->send();
            return;
        }

        $role = PrazzuRole::query()->find($this->selectedRoleId);

        if (! $role) {
            Notification::make()->title('Perfil não encontrado.')->danger()->send();
            return;
        }

        $auditService = app(PrazzuPermissionAuditService::class);
        $beforeSnapshot = $auditService->rolePermissionSnapshot($role);

        DB::transaction(function () use ($role, $permissionService): void {
            foreach ($permissionService->defaultPermissionMatrix() as $module => $actions) {
                foreach ($actions as $action) {
                    $allowed = (bool) data_get($this->rolePermissions, $module . '.' . $action, false);

                    if ($allowed) {
                        PrazzuPermission::query()->updateOrCreate(
                            [
                                'role_id' => $role->id,
                                'module' => $module,
                                'action' => $action,
                                'scope' => 'empresa',
                            ],
                            [
                                'name' => $this->permissionName($module, $action, $permissionService),
                            ]
                        );
                    } else {
                        PrazzuPermission::query()
                            ->where('role_id', $role->id)
                            ->where('module', $module)
                            ->where('action', $action)
                            ->where('scope', 'empresa')
                            ->delete();
                    }
                }
            }
        });

        $this->syncLegacyPermissionRules($role, $permissionService);

        $auditService->record('role.permissions.updated', [
            'role_id' => $role->id,
            'reason' => 'Matriz de permissões do perfil atualizada.',
            'before_payload' => ['permissions' => $beforeSnapshot],
            'after_payload' => ['permissions' => $auditService->rolePermissionSnapshot($role)],
        ]);

        $this->forgetPermissionCache();

        Notification::make()->title('Matriz de permissões do perfil atualizada.')->success()->send();
    }

    public function toggleRoleStatus(int $roleId): void
    {
        if (! $this->ensureCanDo('governanca.edit')) {
            return;
        }

        $role = PrazzuRole::query()->find($roleId);

        if (! $role) {
            return;
        }

        $before = $role->only(['active']);
        $role->update(['active' => ! $role->active]);

        app(PrazzuPermissionAuditService::class)->record('role.status.updated', [
            'role_id' => $role->id,
            'reason' => 'Status do perfil alterado.',
            'before_payload' => $before,
            'after_payload' => $role->only(['active']),
        ]);

        $this->forgetPermissionCache();

        Notification::make()->title('Status do perfil atualizado.')->success()->send();
    }

    public function assignRoleToUser(): void
    {
        if (! $this->ensureCanDo('governanca.edit')) {
            return;
        }

        if (! $this->selectedUserId || ! $this->assignRoleId || ! CachedSchema::hasTable('prazzu_user_roles')) {
            Notification::make()->title('Selecione usuário e perfil.')->warning()->send();
            return;
        }

        $userRole = PrazzuUserRole::query()->firstOrCreate([
            'user_id' => $this->selectedUserId,
            'role_id' => $this->assignRoleId,
        ]);

        app(PrazzuPermissionAuditService::class)->record('user.role.assigned', [
            'target_user_id' => $this->selectedUserId,
            'role_id' => $this->assignRoleId,
            'reason' => 'Perfil vinculado ao usuário.',
            'after_payload' => $userRole->only(['user_id', 'role_id']),
        ]);

        app(PrazzuPermissionService::class)->flushUserCache((int) $this->selectedUserId);

        Notification::make()->title('Perfil vinculado ao usuário.')->success()->send();
    }

    public function removeUserRole(int $userRoleId): void
    {
        if (! $this->ensureCanDo('governanca.edit')) {
            return;
        }

        $record = PrazzuUserRole::query()->find($userRoleId);
        $userId = $record?->user_id;
        $before = $record?->only(['user_id', 'role_id']);
        $roleId = $record?->role_id;
        $record?->delete();

        if ($before) {
            app(PrazzuPermissionAuditService::class)->record('user.role.removed', [
                'target_user_id' => $userId,
                'role_id' => $roleId,
                'reason' => 'Perfil removido do usuário.',
                'before_payload' => $before,
            ]);
        }

        if ($userId) {
            app(PrazzuPermissionService::class)->flushUserCache((int) $userId);
        }

        Notification::make()->title('Perfil removido do usuário.')->success()->send();
    }

    public function saveUserOverride(): void
    {
        if (! $this->ensureCanDo('governanca.edit')) {
            return;
        }

        if (! $this->selectedUserId || ! CachedSchema::hasTable('prazzu_user_permissions')) {
            Notification::make()->title('Selecione um usuário válido.')->warning()->send();
            return;
        }

        $lookup = [
            'user_id' => $this->selectedUserId,
            'module' => $this->overrideModule,
            'action' => $this->overrideAction,
            'scope' => $this->overrideScope ?: 'empresa',
        ];

        $before = PrazzuUserPermission::query()->where($lookup)->first()?->toArray();

        $override = PrazzuUserPermission::query()->updateOrCreate(
            $lookup,
            [
                'allowed' => $this->overrideAllowed,
                'reason' => trim($this->overrideReason) ?: null,
                'created_by' => Auth::id(),
            ]
        );

        app(PrazzuPermissionAuditService::class)->record('user.override.saved', [
            'target_user_id' => $this->selectedUserId,
            'module' => $this->overrideModule,
            'action' => $this->overrideAction,
            'scope' => $this->overrideScope ?: 'empresa',
            'allowed' => $this->overrideAllowed,
            'reason' => trim($this->overrideReason) ?: 'Exceção individual salva.',
            'before_payload' => $before,
            'after_payload' => $override->toArray(),
        ]);

        $this->overrideReason = '';
        app(PrazzuPermissionService::class)->flushUserCache((int) $this->selectedUserId);

        Notification::make()->title('Exceção individual salva.')->success()->send();
    }

    public function removeUserOverride(int $overrideId): void
    {
        if (! $this->ensureCanDo('governanca.edit')) {
            return;
        }

        $record = PrazzuUserPermission::query()->find($overrideId);
        $userId = $record?->user_id;
        $before = $record?->toArray();
        $record?->delete();

        if ($before) {
            app(PrazzuPermissionAuditService::class)->record('user.override.removed', [
                'target_user_id' => $userId,
                'module' => $before['module'] ?? null,
                'action' => $before['action'] ?? null,
                'scope' => $before['scope'] ?? null,
                'allowed' => $before['allowed'] ?? null,
                'reason' => 'Exceção individual removida.',
                'before_payload' => $before,
            ]);
        }

        if ($userId) {
            app(PrazzuPermissionService::class)->flushUserCache((int) $userId);
        }

        Notification::make()->title('Exceção individual removida.')->success()->send();
    }

    protected function getViewData(): array
    {
        $permissionService = app(PrazzuPermissionService::class);
        $auditService = app(PrazzuPermissionAuditService::class);
        $selectedRole = $this->selectedRoleId ? PrazzuRole::query()->find($this->selectedRoleId) : null;
        $selectedUser = $this->selectedUserId ? User::query()->find($this->selectedUserId) : null;

        return [
            'permissions' => $this->permissionFlags('governanca'),
            'roles' => CachedSchema::hasTable('prazzu_roles') ? PrazzuRole::query()->orderByDesc('active')->orderBy('name')->get() : collect(),
            'users' => User::query()->orderBy('name')->limit(200)->get(),
            'selectedRole' => $selectedRole,
            'selectedUser' => $selectedUser,
            'modules' => PrazzuPermissionService::MODULES,
            'actions' => PrazzuPermissionService::ACTIONS,
            'matrix' => $permissionService->defaultPermissionMatrix(),
            'roleStats' => $this->roleStats($permissionService),
            'userRoles' => $selectedUser && CachedSchema::hasTable('prazzu_user_roles')
                ? PrazzuUserRole::query()->with('role')->where('user_id', $selectedUser->id)->get()
                : collect(),
            'userOverrides' => $selectedUser && CachedSchema::hasTable('prazzu_user_permissions')
                ? PrazzuUserPermission::query()->where('user_id', $selectedUser->id)->orderBy('module')->orderBy('action')->get()
                : collect(),
            'effectivePermissions' => $auditService->userEffectivePermissions($selectedUser, $permissionService),
            'permissionAudits' => $auditService->recent(40),
            'legacyRules' => CachedSchema::hasTable('prazzu_permission_rules')
                ? PrazzuPermissionRule::query()->orderBy('role')->orderBy('module')->get()
                : collect(),
            'readiness' => $this->readiness(),
        ];
    }

    private function hydrateRolePermissions(PrazzuPermissionService $permissionService): void
    {
        $permissions = [];

        foreach ($permissionService->defaultPermissionMatrix() as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[$module][$action] = false;
            }
        }

        if ($this->selectedRoleId && CachedSchema::hasTable('prazzu_permissions')) {
            PrazzuPermission::query()
                ->where('role_id', $this->selectedRoleId)
                ->where('scope', 'empresa')
                ->get(['module', 'action'])
                ->each(function (PrazzuPermission $permission) use (&$permissions): void {
                    if (isset($permissions[$permission->module][$permission->action])) {
                        $permissions[$permission->module][$permission->action] = true;
                    }
                });
        }

        $this->rolePermissions = $permissions;
    }

    private function syncLegacyPermissionRules(PrazzuRole $role, PrazzuPermissionService $permissionService): void
    {
        if (! CachedSchema::hasTable('prazzu_permission_rules')) {
            return;
        }

        foreach ($permissionService->defaultPermissionMatrix() as $module => $actions) {
            PrazzuPermissionRule::query()->updateOrCreate(
                ['role' => $this->normalizeRoleForLegacy($role->name), 'module' => $module],
                [
                    'can_view' => (bool) data_get($this->rolePermissions, $module . '.view', false),
                    'can_create' => (bool) data_get($this->rolePermissions, $module . '.create', false),
                    'can_update' => (bool) data_get($this->rolePermissions, $module . '.edit', false),
                    'can_delete' => (bool) data_get($this->rolePermissions, $module . '.delete', false),
                    'scope' => 'empresa',
                ]
            );
        }
    }

    private function roleStats(PrazzuPermissionService $permissionService): array
    {
        $stats = [];

        if (! CachedSchema::hasTable('prazzu_permissions')) {
            return $stats;
        }

        foreach (PrazzuRole::query()->orderBy('name')->get() as $role) {
            $total = 0;
            $active = 0;

            foreach ($permissionService->defaultPermissionMatrix() as $module => $actions) {
                foreach ($actions as $action) {
                    $total++;
                }
            }

            $active = PrazzuPermission::query()->where('role_id', $role->id)->where('scope', 'empresa')->count();

            $stats[$role->id] = [
                'active' => $active,
                'total' => $total,
                'percent' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
            ];
        }

        return $stats;
    }

    private function readiness(): array
    {
        return [
            ['label' => 'Perfis cadastráveis', 'ok' => CachedSchema::hasTable('prazzu_roles')],
            ['label' => 'Permissões por perfil', 'ok' => CachedSchema::hasTable('prazzu_permissions')],
            ['label' => 'Vínculo usuário x perfil', 'ok' => CachedSchema::hasTable('prazzu_user_roles')],
            ['label' => 'Exceções individuais', 'ok' => CachedSchema::hasTable('prazzu_user_permissions')],
            ['label' => 'Auditoria de permissões', 'ok' => CachedSchema::hasTable('prazzu_permission_audits')],
            ['label' => 'Compatibilidade com regras antigas', 'ok' => CachedSchema::hasTable('prazzu_permission_rules')],
        ];
    }

    private function permissionName(string $module, string $action, PrazzuPermissionService $permissionService): string
    {
        $moduleName = PrazzuPermissionService::MODULES[$module] ?? Str::headline($module);
        $actionName = PrazzuPermissionService::ACTIONS[$action] ?? Str::headline($action);

        return $moduleName . ' - ' . $actionName;
    }

    private function normalizeRoleForLegacy(string $role): string
    {
        return Str::of($role)->ascii()->lower()->replace([' ', '-'], '_')->squish()->toString();
    }

    private function forgetPermissionCache(): void
    {
        Cache::flush();
    }

    private function ensureTablesReady(): void
    {
        if (! CachedSchema::hasTable('prazzu_roles') || ! CachedSchema::hasTable('prazzu_permissions')) {
            Notification::make()
                ->title('Estrutura de permissões incompleta')
                ->body('Execute o SQL/migration do lote 1 antes de configurar os perfis.')
                ->warning()
                ->send();
        }
    }

    private function seedStarterRolesIfEmpty(): void
    {
        if (! CachedSchema::hasTable('prazzu_roles') || PrazzuRole::query()->exists()) {
            return;
        }

        foreach ([
            'Administrador' => 'Acesso completo ao ambiente da empresa.',
            'Gestor' => 'Gestão operacional com aprovações.',
            'Supervisor' => 'Revisão, acompanhamento e aprovações limitadas.',
            'Analista' => 'Execução operacional sem exclusões críticas.',
            'Assistente' => 'Rotina básica e apoio operacional.',
            'Cliente' => 'Acesso restrito ao portal e documentos próprios.',
        ] as $name => $description) {
            PrazzuRole::query()->create(compact('name', 'description') + ['active' => true]);
        }
    }
}
