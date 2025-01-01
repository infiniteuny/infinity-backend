<?php

namespace App\Policies;

use App\Models\CoreTeam;
use App\Models\User;

class CoreTeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-core-team');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CoreTeam $coreTeam): bool
    {
        return $user->can('read-core-team');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-core-team');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CoreTeam $coreTeam): bool
    {
        return $user->can('update-core-team');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CoreTeam $coreTeam): bool
    {
        return $user->can('delete-core-team');
    }
}
