<?php

namespace App\Policies;

use App\Models\CompetitionRank;
use App\Models\User;

class CompetitionRankPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-competition-rank');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CompetitionRank $competitionRank): bool
    {
        return $user->can('read-competition-rank');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-competition-rank');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompetitionRank $competitionRank): bool
    {
        return $user->can('update-competition-rank');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompetitionRank $competitionRank): bool
    {
        return $user->can('delete-competition-rank');
    }
}
