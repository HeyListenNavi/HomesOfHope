<?php

namespace App\Policies;

use App\Models\FamilyProfile;
use App\Models\User;

class FamilyProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('family_profile.view_any');
    }

    public function view(User $user, FamilyProfile $familyProfile): bool
    {
        return $user->can('family_profile.view');
    }

    public function create(User $user): bool
    {
        return $user->can('family_profile.create');
    }

    public function update(User $user, FamilyProfile $familyProfile): bool
    {
        return $user->can('family_profile.update');
    }

    public function delete(User $user, FamilyProfile $familyProfile): bool
    {
        return $user->can('family_profile.delete');
    }
}
