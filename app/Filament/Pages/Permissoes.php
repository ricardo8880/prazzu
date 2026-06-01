<?php

namespace App\Filament\Pages;


use App\Support\CachedSchema;
use App\Models\PrazzuPermission;
use App\Models\PrazzuPermissionRule;
use App\Models\PrazzuRole;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class Permissoes extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-lock-closed';
    protected static string | UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Permissões';
    protected static ?string $title = 'Permissões Avançadas';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.permissoes-management';

    public string $roleName = '';
    public string $roleDescription = '';
    public string $roleScope = 'empresa';
    public string $roleModule = 'Operação';
    public bool $canView = true;
    public bool $canCreate = false;
    public bool $canUpdate = false;
    public bool $canDelete = false;

    public array $securityRules = [
        'delete_restriction' => true,
        'export_block' => true,
        'private_default' => true,
        'tags_status_admin_only' => true,
    ];

    public function mount(): void
    {
        $this->loadSecurityRules();
    }

    public function createCustomRole(): void
    {
        $name = trim($this->roleName);

        if ($name === '') {
            Notification::make()->title('Informe o nome do cargo personalizado.')->warning()->send();
            return;
        }

        if (! CachedSchema::hasTable('prazzu_roles') || ! CachedSchema::hasTable('prazzu_permission_rules')) {
            Notification::make()->title('As tabelas de permissões avançadas ainda não existem. Execute o SQL estrutural do módulo antes de salvar cargos.')->danger()->send();
            return;
        }

        $module = trim($this->roleModule) ?: 'Operação';

        $role = PrazzuRole::query()->firstOrCreate(
            ['name' => $name],
            ['description' => trim($this->roleDescription) ?: null, 'active' => true]
        );

        if (! $role->wasRecentlyCreated) {
            $role->update([
                'description' => trim($this->roleDescription) ?: $role->description,
                'active' => true,
            ]);
        }

        PrazzuPermissionRule::query()->updateOrCreate(
            ['role' => $name, 'module' => $module],
            [
                'can_view' => $this->canView,
                'can_create' => $this->canCreate,
                'can_update' => $this->canUpdate,
                'can_delete' => $this->canDelete,
                'scope' => $this->roleScope,
            ]
        );

        $this->roleName = '';
        $this->roleDescription = '';
        $this->roleScope = 'empresa';
        $this->roleModule = 'Operação';
        $this->canView = true;
        $this->canCreate = false;
        $this->canUpdate = false;
        $this->canDelete = false;

        Notification::make()->title('Cargo personalizado salvo com permissões.')->success()->send();
    }

    public function toggleSecurityRule(string $rule): void
    {
        if (! array_key_exists($rule, $this->securityRules)) {
            return;
        }

        $this->securityRules[$rule] = ! $this->securityRules[$rule];
        $this->persistSecurityRule($rule, $this->securityRules[$rule]);

        Notification::make()->title('Regra de segurança atualizada.')->success()->send();
    }

    protected function getViewData(): array
    {
        return [
            'roles' => CachedSchema::hasTable('prazzu_roles') ? PrazzuRole::query()->orderByDesc('active')->orderBy('name')->get() : collect(),
            'permissionRules' => CachedSchema::hasTable('prazzu_permission_rules') ? PrazzuPermissionRule::query()->orderBy('role')->orderBy('module')->get() : collect(),
            'sensitivePermissions' => CachedSchema::hasTable('prazzu_permissions') ? PrazzuPermission::query()->orderBy('module')->orderBy('action')->get() : collect(),
            'moduleSummary' => CachedSchema::hasTable('prazzu_permission_rules') ? PrazzuPermissionRule::query()->selectRaw('module, COUNT(*) as total, SUM(can_view) as view_total, SUM(can_create) as create_total, SUM(can_update) as update_total, SUM(can_delete) as delete_total')->groupBy('module')->orderBy('module')->get() : collect(),
            'permissionChecklist' => $this->permissionChecklist(),
            'cards' => [
                'delete_restriction' => ['title' => 'Restrição de Exclusão', 'description' => 'Impede membros comuns de excluir Listas, Pastas, Espaços e itens sensíveis.', 'on' => 'Somente Admin/Gestor pode excluir', 'off' => 'Membros podem excluir'],
                'export_block' => ['title' => 'Bloqueio de Exportação', 'description' => 'Bloqueia exportação CSV/Excel para proteger dados da empresa.', 'on' => 'Exportação restrita', 'off' => 'Exportação liberada'],
                'private_default' => ['title' => 'Visualização Private vs Public', 'description' => 'Define se novos espaços nascem privados ou públicos por padrão.', 'on' => 'Privado por padrão', 'off' => 'Público por padrão'],
                'tags_status_admin_only' => ['title' => 'Gestão de Tags e Status', 'description' => 'Centraliza criação de tags/status para evitar bagunça no fluxo.', 'on' => 'Admin/Gestor controla', 'off' => 'Todos podem criar'],
            ],
        ];
    }

    private function loadSecurityRules(): void
    {
        if (! CachedSchema::hasTable('prazzu_permissions')) {
            return;
        }

        $this->securityRules['delete_restriction'] = PrazzuPermission::query()->where('module', 'segurança')->where('action', 'delete')->where('scope', 'admin_only')->exists();
        $this->securityRules['export_block'] = PrazzuPermission::query()->where('module', 'segurança')->where('action', 'export')->where('scope', 'admin_only')->exists();
        $this->securityRules['private_default'] = PrazzuPermission::query()->where('module', 'segurança')->where('action', 'visibility')->where('scope', 'private_default')->exists();
        $this->securityRules['tags_status_admin_only'] = PrazzuPermission::query()->where('module', 'workflow')->where('action', 'manage_tags_status')->where('scope', 'admin_or_gestor')->exists();
    }

    private function persistSecurityRule(string $rule, bool $enabled): void
    {
        $map = [
            'delete_restriction' => ['Bloqueio de exclusão para membros comuns', 'segurança', 'delete', 'admin_only'],
            'export_block' => ['Bloqueio de exportação de dados', 'segurança', 'export', 'admin_only'],
            'private_default' => ['Visibilidade padrão privada', 'segurança', 'visibility', 'private_default'],
            'tags_status_admin_only' => ['Gestão centralizada de tags e status', 'workflow', 'manage_tags_status', 'admin_or_gestor'],
        ];

        if (! CachedSchema::hasTable('prazzu_permissions')) {
            return;
        }

        [$name, $module, $action, $scope] = $map[$rule];

        if ($enabled) {
            $permissionData = ['name' => $name];

            if (CachedSchema::hasColumn('prazzu_permissions', 'role_id')) {
                $permissionData['role_id'] = $this->getDefaultSecurityRoleId();
            }

            PrazzuPermission::query()->firstOrCreate(
                ['module' => $module, 'action' => $action, 'scope' => $scope],
                $permissionData
            );

            return;
        }

        PrazzuPermission::query()->where('module', $module)->where('action', $action)->where('scope', $scope)->delete();
    }


    private function getDefaultSecurityRoleId(): ?int
    {
        if (! CachedSchema::hasTable('prazzu_roles')) {
            return null;
        }

        $role = PrazzuRole::query()->firstOrCreate(
            ['name' => 'Administrador'],
            [
                'description' => 'Cargo padrão usado pelas regras avançadas de segurança.',
                'active' => true,
            ]
        );

        return $role->id ? (int) $role->id : null;
    }

    private function permissionChecklist(): array
    {
        $rules = CachedSchema::hasTable('prazzu_permission_rules') ? PrazzuPermissionRule::query()->get() : collect();
        $sensitive = CachedSchema::hasTable('prazzu_permissions') ? PrazzuPermission::query()->get() : collect();

        return [
            ['label' => 'Menus por permissão', 'ok' => $rules->where('can_view', true)->count() > 0, 'hint' => 'Use regras com Visualizar para orientar exibição de módulos.'],
            ['label' => 'Ações por permissão', 'ok' => $rules->where('can_create', true)->count() + $rules->where('can_update', true)->count() + $rules->where('can_delete', true)->count() > 0, 'hint' => 'Criar, editar e excluir ficam explícitos na matriz.'],
            ['label' => 'Rotas protegidas', 'ok' => $sensitive->where('module', 'segurança')->count() > 0, 'hint' => 'Regras sensíveis dão base para proteger rotas críticas.'],
            ['label' => 'Uploads por permissão', 'ok' => $rules->whereIn('module', ['Documentos', 'Operação', 'Anexos'])->where('can_update', true)->count() > 0, 'hint' => 'Controle upload em módulos documentais com permissão de edição.'],
            ['label' => 'Empresa/Tenant', 'ok' => $rules->whereIn('scope', ['empresa', 'responsável/equipe', 'compartilhado'])->count() > 0, 'hint' => 'Escopo define até onde o usuário pode atuar.'],
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
