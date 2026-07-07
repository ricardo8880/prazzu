<?php

namespace App\Policies;

use App\Models\ItemControle;
use App\Models\User;
use App\Support\PrazzuAccessControl;

class ItemControlePolicy
{
    public function viewAny(User $user): bool
    {
        return PrazzuAccessControl::canAccessPage('tarefas.view', $user);
    }

    public function view(User $user, ItemControle $item): bool
    {
        return PrazzuAccessControl::can('tarefas.view', $user) && $item->canBeAccessedBy($user);
    }

    public function create(User $user): bool
    {
        return PrazzuAccessControl::canAccessPage('tarefas.create', $user);
    }

    public function update(User $user, ItemControle $item): bool
    {
        return PrazzuAccessControl::can('tarefas.edit', $user) && $item->canBeModifiedBy($user);
    }

    public function delete(User $user, ItemControle $item): bool
    {
        return PrazzuAccessControl::can('tarefas.delete', $user) && $item->canBeModifiedBy($user);
    }
}
