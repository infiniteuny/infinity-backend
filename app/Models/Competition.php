<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Competition extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'shortname',
        'description',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function competitionInstances(): HasMany
    {
        return $this->hasMany(CompetitionInstance::class);
    }

    public function fundApplications(): HasManyThrough
    {
        return $this->hasManyThrough(
            FundApplication::class,
            CompetitionInstance::class,
            'competition_id',
            'competition_instance_id',
            'id',
            'id',
        );
    }

    public function achievements(): HasManyThrough
    {
        return $this->hasManyThrough(
            Achievement::class,
            CompetitionInstance::class,
            'competition_id',
            'competition_instance_id',
            'id',
            'id',
        );
    }
}
