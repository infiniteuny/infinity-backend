<?php

namespace App\Policies;

use App\Models\CompetitionScale;
use App\Models\User;

class CompetitionScalePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-competition-scale');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CompetitionScale $competitionScale): bool
    {
        return $user->can('read-competition-scale');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-competition-scale');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompetitionScale $competitionScale): bool
    {
        return $user->can('update-competition-scale');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompetitionScale $competitionScale): bool
    {
        return $user->can('delete-competition-scale');
    }
}
