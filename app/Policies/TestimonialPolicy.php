<?php

namespace App\Policies;

use App\Models\User;

class TestimonialPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-testimonial');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can('update-testimonial');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->can('delete-testimonial');
    }
}
