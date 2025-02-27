<?php

namespace App\Policies;

use App\Models\CommunityGroupMember;
use App\Models\User;

class CommunityGroupMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-community-group-member');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CommunityGroupMember $communityGroupMember): bool
    {
        return $user->can('read-community-group-member');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-community-group-member');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CommunityGroupMember $communityGroupMember): bool
    {
        return $user->can('update-community-group-member');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CommunityGroupMember $communityGroupMember): bool
    {
        return $user->can('delete-community-group-member');
    }
}
