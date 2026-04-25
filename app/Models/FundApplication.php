<?php

namespace App\Models;

use App\Casts\Blob;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundApplication extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'competition_instance_id',
        'competition_team_type_id',
        'competition_scale_id',
        'competition_branch',
        'competition_start_date',
        'competition_end_date',
        'letter_of_acceptance',
        'proposal',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'competition_start_date' => 'datetime',
        'competition_end_date' => 'datetime',
        'letter_of_acceptance' => Blob::class,
        'proposal' => Blob::class,
    ];

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function competitionInstance(): BelongsTo
    {
        return $this->belongsTo(CompetitionInstance::class);
    }

    public function competitionScale(): BelongsTo
    {
        return $this->belongsTo(CompetitionScale::class);
    }
}
