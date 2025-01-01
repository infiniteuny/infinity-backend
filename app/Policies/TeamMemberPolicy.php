<?php

namespace App\Policies;

use App\Models\TeamMember;
use App\Models\User;

class TeamMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-team-member');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TeamMember $teamMember): bool
    {
        return $user->can('read-team-member');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-team-member');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TeamMember $teamMember): bool
    {
        return $user->can('update-team-member');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TeamMember $teamMember): bool
    {
        return $user->can('delete-team-member');
    }
}
