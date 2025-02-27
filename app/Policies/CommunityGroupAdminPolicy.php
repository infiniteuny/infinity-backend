<?php

namespace App\Policies;

use App\Models\CommunityGroupAdmin;
use App\Models\User;

class CommunityGroupAdminPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-community-group-admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CommunityGroupAdmin $communityGroupAdmin): bool
    {
        return $user->can('read-community-group-admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-community-group-admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CommunityGroupAdmin $communityGroupAdmin): bool
    {
        return $user->can('update-community-group-admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CommunityGroupAdmin $communityGroupAdmin): bool
    {
        return $user->can('delete-community-group-admin');
    }
}
