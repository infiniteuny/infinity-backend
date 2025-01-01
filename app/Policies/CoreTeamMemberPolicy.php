<?php

namespace App\Policies;

use App\Models\CoreTeamMember;
use App\Models\User;

class CoreTeamMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-core-team-member');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CoreTeamMember $coreTeamMember): bool
    {
        return $user->can('read-core-team-member');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-core-team-member');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CoreTeamMember $coreTeamMember): bool
    {
        return $user->can('update-core-team-member');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CoreTeamMember $coreTeamMember): bool
    {
        return $user->can('delete-core-team-member');
    }
}
