<?php

namespace App\Policies;

use App\Models\Achievement;
use App\Models\User;

class AchievementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-achievement');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Achievement $achievement): bool
    {
        return $user->can('read-achievement') ||
            ($user->can('read-own-achievement') &&
            $achievement->team->leader_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-achievement') || $user->can('create-own-achievement');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Achievement $achievement): bool
    {
        return $user->can('update-achievement') ||
            ($user->can('update-own-achievement') &&
            $achievement->team->leader_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Achievement $achievement): bool
    {
        return $user->can('delete-achievement') ||
            ($user->can('delete-own-achievement') &&
            $achievement->team->leader_id === $user->id);
    }
}
