<?php

namespace App\Policies;

use App\Models\Persona;
use App\Models\User;

class PersonaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-persona');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Persona $persona): bool
    {
        return $user->can('read-persona');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-persona');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Persona $persona): bool
    {
        return $user->can('update-persona');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Persona $persona): bool
    {
        return $user->can('delete-persona');
    }
}
