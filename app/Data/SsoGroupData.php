<?php

namespace App\Data;

use App\Models\Group;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class SsoGroupData extends Data
{
    public function __construct(
        #[MapName('pk')]
        public string|null|Optional $sso_id,
        #[MapName('parents')]
        public array|Optional $sso_parent_ids,
        #[MapName('users')]
        /** @var string[] */
        public array $user_ids,
        public string|Optional $name,
        public bool|Optional $is_superuser,
    ) {}

    public static function fromModel(Group $group): self
    {
        return new self(
            sso_id: $group->sso_id,
            sso_parent_ids: Optional::create(),
            user_ids: $group->users->pluck('sso_id')->map('intval')->toArray(),
            name: $group->name,
            is_superuser: Optional::create(),
        );
    }
}
