<?php

namespace App\Policies;

use App\Models\CompetitionTimeRange;
use App\Models\User;

class CompetitionTimeRangePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-competition-time-range');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CompetitionTimeRange $competitionTimeRange): bool
    {
        return $user->can('read-competition-time-range');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-competition-time-range');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompetitionTimeRange $competitionTimeRange): bool
    {
        return $user->can('update-competition-time-range');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompetitionTimeRange $competitionTimeRange): bool
    {
        return $user->can('delete-competition-time-range');
    }
}
