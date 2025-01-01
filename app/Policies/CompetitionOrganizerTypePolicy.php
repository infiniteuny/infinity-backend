<?php

namespace App\Policies;

use App\Models\CompetitionOrganizerType;
use App\Models\User;

class CompetitionOrganizerTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-competition-organizer-type');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CompetitionOrganizerType $competitionOrganizerType): bool
    {
        return $user->can('read-competition-organizer-type');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-competition-organizer-type');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompetitionOrganizerType $competitionOrganizerType): bool
    {
        return $user->can('update-competition-organizer-type');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompetitionOrganizerType $competitionOrganizerType): bool
    {
        return $user->can('delete-competition-organizer-type');
    }
}
