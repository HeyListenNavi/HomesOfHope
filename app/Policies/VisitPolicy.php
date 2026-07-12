<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('visit.view_any');
    }

    public function view(User $user, Visit $visit): bool
    {
        return $user->can('visit.view');
    }

    public function create(User $user): bool
    {
        return $user->can('visit.create');
    }

    public function update(User $user, Visit $visit): bool
    {
        return $user->can('visit.update');
    }

    public function delete(User $user, Visit $visit): bool
    {
        return $user->can('visit.delete');
    }
}
