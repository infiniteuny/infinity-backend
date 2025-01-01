<?php

namespace App\Policies;

use App\Models\CompetitionOutput;
use App\Models\User;

class CompetitionOutputPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-competition-output');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CompetitionOutput $competitionOutput): bool
    {
        return $user->can('read-competition-output');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-competition-output');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompetitionOutput $competitionOutput): bool
    {
        return $user->can('update-competition-output');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompetitionOutput $competitionOutput): bool
    {
        return $user->can('delete-competition-output');
    }
}
