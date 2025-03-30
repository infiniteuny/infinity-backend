<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserPermission;

class UserPermissionPolicy
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
    public function create(User $user, User $model): bool
    {
        return $user->can('create-user-permission') ||
            ($user->can('create-own-user-permission') && $user->id === $model->id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserPermission $userPermission): bool
    {
        return $user->can('update-user-permission') ||
            ($user->can('update-own-user-permission') && $user->id === $userPermission->user_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserPermission $userPermission): bool
    {
        return $user->can('delete-user-permission') ||
            ($user->can('delete-own-user-permission') && $user->id === $userPermission->user_id);
    }
}
