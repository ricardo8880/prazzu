<?php

namespace Tests\Feature\Permissoes;

use App\Filament\Pages\Permissoes;
use App\Models\PrazzuPermissionAudit;
use App\Models\PrazzuRole;
use App\Models\PrazzuUserPermission;
use App\Models\PrazzuUserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PermissoesPageSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_admin_cannot_assign_role_to_user_from_another_company(): void
    {
        $admin = $this->makeUser(['role' => 'admin', 'empresa_id' => 100]);
        $outsideUser = $this->makeUser(['role' => 'user', 'empresa_id' => 200]);
        $role = PrazzuRole::query()->create(['name' => 'Analista', 'active' => true]);

        $this->actingAs($admin);

        $page = new Permissoes();
        $page->selectedUserId = $outsideUser->id;
        $page->assignRoleId = $role->id;
        $page->assignRoleToUser();

        $this->assertDatabaseMissing('prazzu_user_roles', [
            'user_id' => $outsideUser->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_company_admin_can_assign_role_to_user_from_same_company(): void
    {
        $admin = $this->makeUser(['role' => 'admin', 'empresa_id' => 100]);
        $insideUser = $this->makeUser(['role' => 'user', 'empresa_id' => 100]);
        $role = PrazzuRole::query()->create(['name' => 'Analista', 'active' => true]);

        $this->actingAs($admin);

        $page = new Permissoes();
        $page->selectedUserId = $insideUser->id;
        $page->assignRoleId = $role->id;
        $page->assignRoleToUser();

        $this->assertDatabaseHas('prazzu_user_roles', [
            'user_id' => $insideUser->id,
            'role_id' => $role->id,
        ]);
        $this->assertDatabaseHas('prazzu_permission_audits', [
            'target_user_id' => $insideUser->id,
            'role_id' => $role->id,
            'event' => 'user.role.assigned',
        ]);
    }

    public function test_invalid_user_override_is_not_saved(): void
    {
        $admin = $this->makeUser(['role' => 'admin', 'empresa_id' => 100]);
        $insideUser = $this->makeUser(['role' => 'user', 'empresa_id' => 100]);

        $this->actingAs($admin);

        $page = new Permissoes();
        $page->selectedUserId = $insideUser->id;
        $page->overrideModule = 'relatorios';
        $page->overrideAction = 'delete';
        $page->overrideScope = 'empresa';
        $page->overrideAllowed = true;
        $page->saveUserOverride();

        $this->assertDatabaseMissing('prazzu_user_permissions', [
            'user_id' => $insideUser->id,
            'module' => 'relatorios',
            'action' => 'delete',
        ]);
    }

    public function test_valid_user_override_is_saved_and_audited(): void
    {
        $admin = $this->makeUser(['role' => 'admin', 'empresa_id' => 100]);
        $insideUser = $this->makeUser(['role' => 'user', 'empresa_id' => 100]);

        $this->actingAs($admin);

        $page = new Permissoes();
        $page->selectedUserId = $insideUser->id;
        $page->overrideModule = 'relatorios';
        $page->overrideAction = 'export';
        $page->overrideScope = 'empresa';
        $page->overrideAllowed = true;
        $page->overrideReason = 'Liberação temporária para fechamento mensal.';
        $page->saveUserOverride();

        $this->assertDatabaseHas('prazzu_user_permissions', [
            'user_id' => $insideUser->id,
            'module' => 'relatorios',
            'action' => 'export',
            'scope' => 'empresa',
            'allowed' => true,
        ]);
        $this->assertDatabaseHas('prazzu_permission_audits', [
            'target_user_id' => $insideUser->id,
            'module' => 'relatorios',
            'action' => 'export',
            'event' => 'user.override.saved',
        ]);
    }

    private function makeUser(array $attributes = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'Usuário Teste ' . uniqid(),
            'email' => uniqid('user_', true) . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'perfil_contabil' => 'assistente',
            'empresa_id' => null,
        ], $attributes));
    }
}
