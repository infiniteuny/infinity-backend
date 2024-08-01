<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email_address',
        'phone_number',
        'student_id',
        'major_id',
        'links',
        'role',
        'start_date',
        'end_date',
        'is_member',
        'is_extraordinary',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function ledTeams()
    {
        return $this->hasMany(Team::class, 'leader_id');
    }

    public function teams()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function coreTeams()
    {
        return $this->hasMany(CoreTeamMember::class);
    }
}
