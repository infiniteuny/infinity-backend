<?php

namespace App\Policies;

use App\Models\Achievement;
use App\Models\User;

class AchievementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-achievement') ||
            $user->can('create-own-achievement');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Achievement $achievement): bool
    {
        return $user->can('update-achievement') ||
            ($user->can('update-own-achievement') && ($achievement->team->leader_id === $user->id ||
            $achievement->team->members()->wherePivot('user_id', $user->id)->exists()));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Achievement $achievement): bool
    {
        return $user->can('delete-achievement') ||
            ($user->can('delete-own-achievement') && $achievement->team->leader_id === $user->id);
    }
}
