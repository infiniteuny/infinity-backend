<?php

namespace App\Models;

use App\Casts\Blob;
use App\Traits\HasBulkCreate;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CommunityGroup extends Model
{
    use HasBulkCreate, HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'priority',
        'description',
        'logo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'logo' => Blob::class,
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
            'community_group_members',
            'community_group_id',
            'user_id',
        )
            ->using(CommunityGroupMember::class)
            ->as('membership')
            ->withPivot([
                'id',
            ])
            ->withTimestamps();
    }
}
