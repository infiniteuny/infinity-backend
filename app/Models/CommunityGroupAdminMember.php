<?php

namespace App\Models;

use App\Casts\Blob;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CommunityGroupAdminMember extends Pivot
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'community_group_admin_members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'community_group_admin_id',
        'community_group_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function communityGroupAdmin(): BelongsTo
    {
        return $this->belongsTo(CommunityGroupAdmin::class);
    }

    public function communityGroup(): BelongsTo
    {
        return $this->belongsTo(CommunityGroup::class);
    }
}
