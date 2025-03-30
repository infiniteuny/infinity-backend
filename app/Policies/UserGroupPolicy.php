<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserGroup;

class UserGroupPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, User $model): bool
    {
        return $user->can('read-user-group') ||
            ($user->can('read-own-user-group') && $user->id === $model->id);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserGroup $userGroup): bool
    {
        return $user->can('read-user-group') ||
            ($user->can('read-own-user-group') && $user->id === $userGroup->user_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-user-group');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can('update-user-group');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->can('delete-user-group');
    }
}
