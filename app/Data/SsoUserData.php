<?php

namespace App\Data;

use App\Models\User;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class SsoUserData extends Data
{
    public function __construct(
        #[MapName('pk')]
        public string|Optional $sso_id,
        public string|Optional $name,
        #[MapName('email')]
        public string|Optional $email_address,
        public bool|Optional $is_active,
        public string|Optional $path,
        public string|Optional $type,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            sso_id: $user->sso_id,
            name: $user->name,
            email_address: $user->email,
            is_active: Optional::create(),
            path: Optional::create(),
            type: Optional::create(),
        );
    }
}
