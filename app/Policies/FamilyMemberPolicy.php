<?php

namespace App\Policies;

use App\Models\FamilyMember;
use App\Models\User;

class FamilyMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('family_member.view_any');
    }

    public function view(User $user, FamilyMember $familyMember): bool
    {
        return $user->can('family_member.view');
    }

    public function create(User $user): bool
    {
        return $user->can('family_member.create');
    }

    public function update(User $user, FamilyMember $familyMember): bool
    {
        return $user->can('family_member.update');
    }

    public function delete(User $user, FamilyMember $familyMember): bool
    {
        return $user->can('family_member.delete');
    }
}
