<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CommunityGroupAdmin extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'year',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'community_group_admin_members',
            'community_group_admin_id',
            'user_id',
        )
            ->using(CommunityGroupAdminMember::class)
            ->withPivot([
                'community_group_id',
                'photo',
                'animation',
            ])
            ->withTimestamps();
    }
}
