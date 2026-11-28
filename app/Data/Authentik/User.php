<?php

namespace App\Data\Authentik;

use App\Models\User as UserModel;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class User extends Data
{
    public function __construct(
        #[MapName('pk')]
        public int|string|null|Optional $sso_id,
        public string|null|Optional $name,
        public string|Optional $username,
        #[MapName('email')]
        public string|null|Optional $email_address,
        public bool|Optional $is_active,
        public string|Optional $path,
        public string|Optional $type,
    ) {}

    public static function fromModel(UserModel $user): self
    {
        return new self(
            sso_id: Optional::create(),
            name: $user->name,
            username: $user->username,
            email_address: $user->email_address,
            is_active: (bool) $user->is_member,
            path: Optional::create(),
            type: Optional::create(),
        );
    }
}
