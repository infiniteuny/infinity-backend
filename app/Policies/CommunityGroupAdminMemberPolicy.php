<?php

namespace App\Policies;

use App\Models\CommunityGroupAdminMember;
use App\Models\User;

class CommunityGroupAdminMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-community-group-admin-member');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CommunityGroupAdminMember $communityGroupAdminMember): bool
    {
        return $user->can('read-community-group-admin-member');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-community-group-admin-member');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CommunityGroupAdminMember $communityGroupAdminMember): bool
    {
        return $user->can('update-community-group-admin-member');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CommunityGroupAdminMember $communityGroupAdminMember): bool
    {
        return $user->can('delete-community-group-admin-member');
    }
}
