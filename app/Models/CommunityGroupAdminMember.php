<?php

namespace App\Models;

use App\Casts\Blob;
use App\Traits\HasUuids;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityGroupAdminMember extends Model
{
    use HasFactory, HasUuids;

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
