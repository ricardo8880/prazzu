<?php

namespace Tests\Feature\Permissoes;

use App\Models\PrazzuPermission;
use App\Models\PrazzuRole;
use App\Models\PrazzuUserPermission;
use App\Models\PrazzuUserRole;
use App\Models\User;
use App\Services\PrazzuPermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrazzuPermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permission_allows_user_and_denies_unknown_permission(): void
    {
        $user = $this->makeUser(['role' => 'user', 'empresa_id' => 10]);
        $role = PrazzuRole::query()->create(['name' => 'Analista', 'active' => true]);

        PrazzuUserRole::query()->create(['user_id' => $user->id, 'role_id' => $role->id]);
        PrazzuPermission::query()->create([
            'role_id' => $role->id,
            'module' => 'clientes',
            'action' => 'view',
            'scope' => 'empresa',
            'name' => 'Clientes - Ver',
        ]);

        $service = app(PrazzuPermissionService::class);

        $this->assertTrue($service->can($user, 'clientes.view'));
        $this->assertFalse($service->can($user, 'relatorios.delete'));
    }

    public function test_direct_user_exception_overrides_role_permission_by_scope(): void
    {
        $user = $this->makeUser(['role' => 'user', 'empresa_id' => 10]);
        $role = PrazzuRole::query()->create(['name' => 'Gestor', 'active' => true]);

        PrazzuUserRole::query()->create(['user_id' => $user->id, 'role_id' => $role->id]);
        PrazzuPermission::query()->create([
            'role_id' => $role->id,
            'module' => 'documentos',
            'action' => 'edit',
            'scope' => 'empresa',
            'name' => 'Documentos - Editar',
        ]);
        PrazzuUserPermission::query()->create([
            'user_id' => $user->id,
            'module' => 'documentos',
            'action' => 'edit',
            'scope' => 'proprio',
            'allowed' => false,
            'reason' => 'Bloqueio em documentos próprios para homologação.',
        ]);

        $service = app(PrazzuPermissionService::class);

        $this->assertFalse($service->can($user, 'documentos.edit', 'proprio'));
        $this->assertTrue($service->can($user, 'documentos.edit', 'empresa'));
    }

    public function test_flush_user_cache_only_removes_permission_keys_for_that_user(): void
    {
        $user = $this->makeUser(['role' => 'user', 'empresa_id' => 10]);
        $otherUser = $this->makeUser(['role' => 'user', 'empresa_id' => 10]);

        Cache::put('prazzu_permission:' . $user->id . ':clientes:view:empresa', true, 300);
        Cache::put('prazzu_permission:' . $otherUser->id . ':clientes:view:empresa', true, 300);
        Cache::put('unrelated_system_cache', 'keep-me', 300);

        app(PrazzuPermissionService::class)->flushUserCache($user);

        $this->assertFalse(Cache::has('prazzu_permission:' . $user->id . ':clientes:view:empresa'));
        $this->assertTrue(Cache::has('prazzu_permission:' . $otherUser->id . ':clientes:view:empresa'));
        $this->assertSame('keep-me', Cache::get('unrelated_system_cache'));
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
