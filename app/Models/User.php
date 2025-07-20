<?php

namespace App\Models;

use App\Traits\HasGroups;
use DateTimeInterface;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Model implements AuthenticatableContract, AuthorizableContract, MustVerifyEmailContract
{
    use Authenticatable, Authorizable, MustVerifyEmail;
    use HasFactory, HasGroups, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
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
        'sso_last_synced_at',
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_active',
    ];

    protected string $guard_name = 'api';

    protected function getDefaultGuardName(): string
    {
        return $this->guard_name;
    }

    protected function isActive(): Attribute
    {
        return new Attribute(
            get: fn () => $this->is_member && $this->start_date <= Carbon::now() &&
                $this->end_date >= Carbon::now(),
        );
    }

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
            ->as('entitlement')
            ->withPivot([
                'id',
            ])
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
            ->as('membership')
            ->withPivot([
                'id',
            ])
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
            ->as('membership')
            ->withPivot([
                'id',
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
            ->as('membership')
            ->withPivot([
                'id',
            ])
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
            ->as('membership')
            ->withPivot([
                'id',
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
