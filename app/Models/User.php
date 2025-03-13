<?php

namespace App\Models;

use App\Traits\HasGroups;
use App\Traits\HasUuids;
use DateTimeInterface;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasGroups, HasUuids, Notifiable;

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
        'start_date',
        'end_date',
        'is_member',
        'is_extraordinary',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'sso_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'links' => 'array',
        'is_member' => 'boolean',
        'is_extraordinary' => 'boolean',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function personas(): HasMany
    {
        return $this->hasMany(UserPersona::class);
    }

    public function communityGroups(): HasMany
    {
        return $this->hasMany(CommunityGroupMember::class);
    }

    public function communityGroupAdmins(): HasMany
    {
        return $this->hasMany(CommunityGroupAdminMember::class);
    }

    public function ledTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'leader_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function coreTeams(): HasMany
    {
        return $this->hasMany(CoreTeamMember::class);
    }
}
