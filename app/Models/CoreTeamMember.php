<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoreTeamMember extends Model
{
    use HasFactory;

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
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coreTeam(): BelongsTo
    {
        return $this->belongsTo(CoreTeam::class);
    }

    public function coreTeamDivision(): BelongsTo
    {
        return $this->belongsTo(CoreTeamDivision::class);
    }
}
