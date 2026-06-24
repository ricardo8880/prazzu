<?php

namespace App\Services;

use App\Models\AuditoriaDetalhada;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuditoriaAccessService
{
    public function canView(?User $user = null): bool
    {
        $user ??= Auth::user();

        if (! $user) {
            return false;
        }

        if ($this->isSuperAdmin($user) || $this->isAdminEmpresa($user)) {
            return true;
        }

        return $this->hasAdvancedPermission($user, 'governanca.view')
            || $this->hasAdvancedPermission($user, 'auditoria.view');
    }

    public function canExport(?User $user = null): bool
    {
        $user ??= Auth::user();

        if (! $user) {
            return false;
        }

        if ($this->isSuperAdmin($user) || $this->isAdminEmpresa($user)) {
            return true;
        }

        return $this->hasAdvancedPermission($user, 'governanca.export')
            || $this->hasAdvancedPermission($user, 'auditoria.export')
            || $this->hasAdvancedPermission($user, 'relatorios.export');
    }

    public function canViewRecord(AuditoriaDetalhada $auditoria, ?User $user = null): bool
    {
        $user ??= Auth::user();

        if (! $this->canView($user)) {
            return false;
        }

        if (! $user) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($auditoria->empresa_id === null) {
            return true;
        }

        return (int) $auditoria->empresa_id === (int) $user->empresa_id;
    }

    public function normalizeEmpresaFilter(?User $user, mixed $empresaId): ?int
    {
        $empresaId = filled($empresaId) && $empresaId !== 'todas' ? (int) $empresaId : null;

        if (! $user) {
            return null;
        }

        if ($this->isSuperAdmin($user)) {
            return $empresaId;
        }

        return filled($user->empresa_id) ? (int) $user->empresa_id : null;
    }

    private function hasAdvancedPermission(User $user, string $permission): bool
    {
        if (method_exists($user, 'hasAdvancedPermission') && $user->hasAdvancedPermission($permission, 'empresa')) {
            return true;
        }

        if (method_exists($user, 'hasAdvancedPermission') && $user->hasAdvancedPermission($permission, 'all')) {
            return true;
        }

        return false;
    }

    private function isSuperAdmin(User $user): bool
    {
        return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
    }

    private function isAdminEmpresa(User $user): bool
    {
        return method_exists($user, 'isAdminEmpresa') && $user->isAdminEmpresa();
    }
}
