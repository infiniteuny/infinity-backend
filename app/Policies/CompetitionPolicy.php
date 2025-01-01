<?php

namespace App\Policies;

use App\Models\Competition;
use App\Models\User;

class CompetitionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-competition');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Competition $competition): bool
    {
        return $user->can('read-competition');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-competition');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Competition $competition): bool
    {
        return $user->can('update-competition');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Competition $competition): bool
    {
        return $user->can('delete-competition');
    }
}
