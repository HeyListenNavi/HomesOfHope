<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user.view_any');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('user.view');
    }

    public function create(User $user): bool
    {
        return $user->can('user.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('user.update');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('user.delete');
    }
}
