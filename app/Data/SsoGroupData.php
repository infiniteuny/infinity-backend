<?php

namespace App\Data;

use App\Models\Group;
use Illuminate\Support\Optional;
use Spatie\LaravelData\Data;

class SsoGroupData extends Data
{
    public function __construct(
        #[MapName('pk')]
        public string|Optional $sso_id,
        #[MapName('parent')]
        public string|Optional $sso_parent_id,
        public string|Optional $name,
        public bool|Optional $is_superuser,
    ) {}

    public static function fromModel(Group $group): self
    {
        return new self(
            sso_id: $group->sso_id,
            sso_parent_id: Optional::create(),
            name: $group->name,
            is_superuser: Optional::create(),
        );
    }
}
