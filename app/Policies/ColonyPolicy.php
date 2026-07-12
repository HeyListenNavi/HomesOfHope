<?php

namespace App\Policies;

use App\Models\Colony;
use App\Models\User;

class ColonyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('colony.view_any');
    }

    public function view(User $user, Colony $colony): bool
    {
        return $user->can('colony.view');
    }

    public function create(User $user): bool
    {
        return $user->can('colony.create');
    }

    public function update(User $user, Colony $colony): bool
    {
        return $user->can('colony.update');
    }

    public function delete(User $user, Colony $colony): bool
    {
        return $user->can('colony.delete');
    }
}
