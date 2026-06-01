<?php

namespace App\Policies;

use App\Models\ItemControle;
use App\Models\User;
use App\Support\PrazzuAccessControl;

class ItemControlePolicy
{
    public function viewAny(User $user): bool
    {
        return PrazzuAccessControl::canUseWorkArea($user);
    }

    public function view(User $user, ItemControle $item): bool
    {
        return $item->canBeAccessedBy($user);
    }

    public function create(User $user): bool
    {
        return PrazzuAccessControl::canUseWorkArea($user);
    }

    public function update(User $user, ItemControle $item): bool
    {
        return $item->canBeModifiedBy($user);
    }

    public function delete(User $user, ItemControle $item): bool
    {
        return $user->isSuperAdmin();
    }
}
