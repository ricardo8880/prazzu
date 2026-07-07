<?php

namespace App\Policies;

use App\Models\User;
use App\Support\PrazzuAccessControl;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return PrazzuAccessControl::canAccessPage('governanca.view', $user);
    }

    public function view(User $user, User $model): bool
    {
        if (! PrazzuAccessControl::can('governanca.view', $user)) {
            return false;
        }

        return PrazzuAccessControl::canAccessCompanyRecord($model, $user);
    }

    public function create(User $user): bool
    {
        return PrazzuAccessControl::canAccessPage('governanca.create', $user);
    }

    public function update(User $user, User $model): bool
    {
        if (! PrazzuAccessControl::can('governanca.edit', $user)) {
            return false;
        }

        return PrazzuAccessControl::canAccessCompanyRecord($model, $user);
    }

    public function delete(User $user, User $model): bool
    {
        return PrazzuAccessControl::can('governanca.delete', $user) && $user->isSuperAdmin();
    }
}
