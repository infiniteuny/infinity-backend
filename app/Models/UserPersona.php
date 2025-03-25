<?php

namespace App\Models;

use App\Traits\HasUuids;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserPersona extends Pivot
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'persona_id',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }
}
