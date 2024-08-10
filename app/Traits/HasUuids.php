<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Concerns\HasUuids as ConcernsHasUuids;
use Symfony\Component\Uid\Uuid;

trait HasUuids
{
    use ConcernsHasUuids;

    /**
     * Generate a new UUID for the model.
     */
    public function newUniqueId(): string
    {
        return (string) Uuid::v7();
    }
}
