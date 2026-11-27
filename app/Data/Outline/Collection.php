<?php

namespace App\Data\Outline;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class Collection extends Data
{
    public function __construct(
        public string|null|Optional $id,
        public string|Optional $name,
        public string|null|Optional $description,
        public string|null|Optional $permission,
        public string|null|Optional $icon,
        public string|null|Optional $color,
        public bool|Optional $sharing,
        public string|null|Optional $url,
        public string|null|Optional $urlId,
    ) {}
}
