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
        return $user->can('view-user-group') ||
            ($user->can('view-own-user-group') && $user->id === $model->id);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserGroup $userGroup): bool
    {
        return $user->can('view-user-group') ||
            ($user->can('view-own-user-group') && $user->id === $userGroup->user_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, User $model): bool
    {
        return $user->can('create-user-group') ||
            ($user->can('create-own-user-group') && $user->id === $model->id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserGroup $userGroup): bool
    {
        return $user->can('update-user-group') ||
            ($user->can('update-own-user-group') && $user->id === $userGroup->user_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserGroup $userGroup): bool
    {
        return $user->can('delete-user-group') ||
            ($user->can('delete-own-user-group') && $user->id === $userGroup->user_id);
    }
}
