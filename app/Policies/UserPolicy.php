<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminEmpresa();
    }

    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) return true;

        return $user->empresa_id === $model->empresa_id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminEmpresa();
    }

    public function update(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) return true;

        return $user->empresa_id === $model->empresa_id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }
}
