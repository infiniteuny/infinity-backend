<?php

namespace App\Policies;

use App\Models\CoreTeamDivision;
use App\Models\User;

class CoreTeamDivisionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-core-team-division');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CoreTeamDivision $coreTeamDivision): bool
    {
        return $user->can('read-core-team-division');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-core-team-division');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CoreTeamDivision $coreTeamDivision): bool
    {
        return $user->can('update-core-team-division');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CoreTeamDivision $coreTeamDivision): bool
    {
        return $user->can('delete-core-team-division');
    }
}
