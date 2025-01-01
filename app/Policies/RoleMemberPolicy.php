<?php

namespace App\Policies;

use App\Models\RoleMember;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RoleMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-role-member');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RoleMember $roleMember): bool
    {
        return $user->can('read-role-member');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-role-member');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RoleMember $roleMember): bool
    {
        return $user->can('update-role-member');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RoleMember $roleMember): bool
    {
        return $user->can('delete-role-member');
    }
}
