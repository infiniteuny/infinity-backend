<?php

namespace App\Data\Outline;

use App\Models\Group as GroupModel;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class Group extends Data
{
    public function __construct(
        public string|null|Optional $id,
        public string|Optional $name,
        public string|null|Optional $description,
        public string|null|Optional $icon,
        public string|null|Optional $color,
        public string|null|Optional $externalId,
        public int|Optional $memberCount,
    ) {
    }

    public static function fromModel(GroupModel $group): self
    {
        return new self(
            id: Optional::create(),
            name: $group->name,
            description: Optional::create(),
            icon: Optional::create(),
            color: Optional::create(),
            externalId: Optional::create(),
            memberCount: Optional::create(),
        );
    }
}
