<?php

namespace App\Policies;

use App\Models\Major;
use App\Models\User;

class MajorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-major');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Major $major): bool
    {
        return $user->can('read-major');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-major');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Major $major): bool
    {
        return $user->can('update-major');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Major $major): bool
    {
        return $user->can('delete-major');
    }
}
