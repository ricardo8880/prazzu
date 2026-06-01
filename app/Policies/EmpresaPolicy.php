<?php

namespace App\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminEmpresa();
    }

    public function view(User $user, Empresa $empresa): bool
    {
        if ($user->isSuperAdmin()) return true;

        return $user->empresa_id === $empresa->id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Empresa $empresa): bool
    {
        if ($user->isSuperAdmin()) return true;

        return $user->empresa_id === $empresa->id;
    }

    public function delete(User $user, Empresa $empresa): bool
    {
        return $user->isSuperAdmin();
    }
}
