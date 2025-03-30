<?php

namespace App\Policies;

use App\Models\FundApplication;
use App\Models\User;

class FundApplicationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-fund-application') ||
            $user->can('read-own-fund-application');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FundApplication $fundApplication): bool
    {
        return $user->can('read-fund-application') ||
            ($user->can('read-own-fund-application') && ($user->id === $fundApplication->team->leader_id ||
            $fundApplication->team->members()->wherePivot('user_id', $user->id)->exists()));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-fund-application') ||
            $user->can('create-own-fund-application');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FundApplication $fundApplication): bool
    {
        return $user->can('update-fund-application') ||
            ($user->can('update-own-fund-application') && ($user->id === $fundApplication->team->leader_id ||
            $fundApplication->team->members()->wherePivot('user_id', $user->id)->exists()));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FundApplication $fundApplication): bool
    {
        return $user->can('delete-fund-application') ||
            ($user->can('delete-own-fund-application') && $user->id === $fundApplication->team->leader_id);
    }
}
