<?php

namespace App\Models;

use App\Casts\Blob;
use App\Traits\HasUuids;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CoreTeamMember extends Pivot
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'core_team_id',
        'core_team_division_id',
        'photo',
        'animation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'photo' => Blob::class,
        'animation' => Blob::class,
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function coreTeamDivision(): BelongsTo
    {
        return $this->belongsTo(CoreTeamDivision::class);
    }
}
