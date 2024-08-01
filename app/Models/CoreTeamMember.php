<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoreTeamMember extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'photo',
        'animation',
        'user_id',
        'core_team_id',
        'core_team_division_id',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coreTeam()
    {
        return $this->belongsTo(CoreTeam::class);
    }

    public function coreTeamDivision()
    {
        return $this->belongsTo(CoreTeamDivision::class);
    }
}
