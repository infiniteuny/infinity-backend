<?php

namespace App\Policies;

use App\Models\UserPersona;
use App\Models\User;

class UserPersonaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-user-persona');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserPersona $userPersona): bool
    {
        return $user->can('read-user-persona');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-user-persona');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserPersona $userPersona): bool
    {
        return $user->can('update-user-persona');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserPersona $userPersona): bool
    {
        return $user->can('delete-user-persona');
    }
}
