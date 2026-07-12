<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('group.view_any');
    }

    public function view(User $user, Group $group): bool
    {
        return $user->can('group.view');
    }

    public function create(User $user): bool
    {
        return $user->can('group.create');
    }

    public function update(User $user, Group $group): bool
    {
        return $user->can('group.update');
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->can('group.delete');
    }
}
