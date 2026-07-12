<?php

namespace App\Policies;

use App\Models\Stage;
use App\Models\User;

class StagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('stage.view_any');
    }

    public function view(User $user, Stage $stage): bool
    {
        return $user->can('stage.view');
    }

    public function create(User $user): bool
    {
        return $user->can('stage.create');
    }

    public function update(User $user, Stage $stage): bool
    {
        return $user->can('stage.update');
    }

    public function delete(User $user, Stage $stage): bool
    {
        return $user->can('stage.delete');
    }
}
