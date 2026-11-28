<?php

namespace App\Data\Outline;

use App\Enums\OutlineUserRole;
use App\Models\User as UserModel;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class User extends Data
{
    public function __construct(
        public string|null|Optional $id,
        public string|Optional $name,
        public string|null|Optional $email,
        public string|null|Optional $role,
        public bool|Optional $isSuspended,
        public string|null|Optional $avatarUrl,
    ) {}

    public static function fromModel(UserModel $user, OutlineUserRole $role = OutlineUserRole::MEMBER): self
    {
        return new self(
            id: Optional::create(),
            name: $user->name,
            email: is_null($user->email_address) ? null : trim((string) $user->email_address),
            role: $role->value,
            isSuspended: Optional::create(),
            avatarUrl: Optional::create(),
        );
    }
}
