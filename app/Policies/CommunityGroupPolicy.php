<?php

namespace App\Policies;

use App\Models\CommunityGroup;
use App\Models\User;

class CommunityGroupPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-community-group');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CommunityGroup $communityGroup): bool
    {
        return $user->can('read-community-group');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-community-group');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CommunityGroup $communityGroup): bool
    {
        return $user->can('update-community-group');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CommunityGroup $communityGroup): bool
    {
        return $user->can('delete-community-group');
    }
}
