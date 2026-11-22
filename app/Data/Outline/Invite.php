<?php

namespace App\Data\Outline;

use App\Enums\OutlineUserRole;
use App\Models\User;
use Spatie\LaravelData\Data;

class Invite extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $role = OutlineUserRole::MEMBER->value,
    ) {
    }

    public static function fromModel(User $user, OutlineUserRole $role = OutlineUserRole::MEMBER): self
    {
        return new self(
            name: $user->name,
            email: $user->email_address,
            role: $role->value,
        );
    }
}
