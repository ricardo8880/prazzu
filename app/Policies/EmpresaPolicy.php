<?php

namespace App\Policies;

use App\Models\Empresa;
use App\Models\User;
use App\Support\PrazzuAccessControl;

class EmpresaPolicy
{
    public function viewAny(User $user): bool
    {
        return PrazzuAccessControl::canAccessPage('configuracoes.view', $user);
    }

    public function view(User $user, Empresa $empresa): bool
    {
        if (! PrazzuAccessControl::can('configuracoes.view', $user)) {
            return false;
        }

        return $user->isSuperAdmin() || (int) $user->empresa_id === (int) $empresa->id;
    }

    public function create(User $user): bool
    {
        return PrazzuAccessControl::can('configuracoes.create', $user) && $user->isSuperAdmin();
    }

    public function update(User $user, Empresa $empresa): bool
    {
        if (! PrazzuAccessControl::can('configuracoes.edit', $user)) {
            return false;
        }

        return $user->isSuperAdmin() || (int) $user->empresa_id === (int) $empresa->id;
    }

    public function delete(User $user, Empresa $empresa): bool
    {
        return PrazzuAccessControl::can('configuracoes.delete', $user) && $user->isSuperAdmin();
    }
}
