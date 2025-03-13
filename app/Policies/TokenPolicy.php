<?php

namespace App\Policies;

use App\Models\Token;
use App\Models\User;

class TokenPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read-token');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Token $token): bool
    {
        return $user->can('read-token') ||
        ($user->can('read-own-token') &&
        $token->user_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Token $token): bool
    {
        return $user->can('delete-token') ||
            ($user->can('delete-own-token') &&
            $token->user_id === $user->id);
    }
}
