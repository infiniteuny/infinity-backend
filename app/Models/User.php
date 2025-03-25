<?php

namespace App\Models;

use App\Traits\HasGroups;
use App\Traits\HasUuids;
use DateTimeInterface;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function personas(): BelongsToMany
    {
        return $this->belongsToMany(
            Persona::class,
            'user_personas',
            'user_id',
            'persona_id',
        )
            ->using(UserPersona::class)
            ->withTimestamps();
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(
            Team::class,
            'team_members',
            'user_id',
            'team_id',
        )
            ->using(TeamMember::class)
            ->withTimestamps();
    }

    public function coreTeams(): BelongsToMany
    {
        return $this->belongsToMany(
            CoreTeam::class,
            'core_team_members',
            'user_id',
            'core_team_id',
        )
            ->using(CoreTeamMember::class)
            ->withPivot([
                'core_team_division_id',
                'photo',
                'animation',
            ])
            ->withTimestamps();
    }

    public function communityGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            CommunityGroup::class,
            'community_group_members',
            'user_id',
            'community_group_id',
        )
            ->using(CommunityGroupMember::class)
            ->withTimestamps();
    }

    public function communityGroupAdmins(): BelongsToMany
    {
        return $this->belongsToMany(
            CommunityGroupAdmin::class,
            'community_group_admin_members',
            'user_id',
            'community_group_admin_id',
        )
            ->using(CommunityGroupAdminMember::class)
            ->withPivot([
                'community_group_id',
                'photo',
                'animation',
            ])
            ->withTimestamps();
    }

    public function ledTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'leader_id');
    }
}
