<?php

namespace App\Data\Outline;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class CollectionGroupMembership extends Data
{
    public function __construct(
        public string|null|Optional $id,
        public string|Optional $groupId,
        public string|null|Optional $collectionId,
        public string|Optional $permission,
    ) {
    }
}
