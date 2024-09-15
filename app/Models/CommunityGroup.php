<?php

namespace App\Models;

use App\Casts\Blob;
use App\Traits\HasBulkCreate;
use App\Traits\HasUuids;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function communityGroupAdminMembers(): HasMany
    {
        return $this->hasMany(CommunityGroupAdminMember::class);
    }

    public function communityGroupMembers(): HasMany
    {
        return $this->hasMany(CommunityGroupMember::class);
    }
}
