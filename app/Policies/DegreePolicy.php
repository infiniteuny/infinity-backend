<?php

namespace App\Policies;

use App\Models\Degree;
use App\Models\User;

class DegreePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-degree');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Degree $degree): bool
    {
        return $user->can('read-degree');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-degree');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Degree $degree): bool
    {
        return $user->can('update-degree');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Degree $degree): bool
    {
        return $user->can('delete-degree');
    }
}
