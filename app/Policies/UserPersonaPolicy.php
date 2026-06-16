<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserPersona;

class UserPersonaPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, User $model): bool
    {
        return $user->can('create-user-persona') ||
            ($user->can('create-own-user-persona') && $user->id === $model->id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserPersona $userPersona): bool
    {
        return $user->can('update-user-persona') ||
            ($user->can('update-own-user-persona') && $user->id === $userPersona->user_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserPersona $userPersona): bool
    {
        return $user->can('delete-user-persona') ||
            ($user->can('delete-own-user-persona') && $user->id === $userPersona->user_id);
    }
}
