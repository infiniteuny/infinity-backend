<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-team') || $user->can('read-own-team');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        return $user->can('read-team') ||
            ($user->can('read-own-team') && ($user->id === $team->leader_id ||
            $team->members()->wherePivot('user_id', $user->id)->exists()));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-team') || $user->can('create-own-team');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        return $user->can('update-team') ||
            ($user->can('update-own-team') && $user->id === $team->leader_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        return $user->can('delete-team') ||
            ($user->can('delete-own-team') && $user->id === $team->leader_id);
    }
}
