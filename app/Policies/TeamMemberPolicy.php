<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;

class TeamMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Team $team): bool
    {
        return $user->can('read-team-member') ||
            ($user->can('read-own-team-member') && ($user->id === $team->leader_id ||
            $team->members()->wherePivot('user_id', $user->id)->exists()));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TeamMember $teamMember): bool
    {
        return $user->can('read-team-member') ||
            ($user->can('read-own-team-member') && ($user->id === $teamMember->user_id ||
            $teamMember->team->leader_id === $user->id ||
            $teamMember->team->members()->wherePivot('user_id', $user->id)->exists()));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Team $team): bool
    {
        return $user->can('create-team-member') ||
            ($user->can('create-own-team-member') && $user->id === $team->leader_id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TeamMember $teamMember): bool
    {
        return $user->can('update-team-member') ||
            ($user->can('update-own-team-member') && ($user->id === $teamMember->user_id ||
            $teamMember->team->leader_id === $user->id));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TeamMember $teamMember): bool
    {
        return $user->can('delete-team-member') ||
            ($user->can('delete-own-team-member') && ($user->id === $teamMember->user_id ||
            $teamMember->team->leader_id === $user->id));
    }
}
