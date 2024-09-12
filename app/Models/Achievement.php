<?php

namespace App\Models;

use App\Casts\Blob;
use App\Traits\HasUuids;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'competition_id',
        'competition_team_type_id',
        'competition_scale_id',
        'competition_time_range_id',
        'competition_output_id',
        'competition_rank_id',
        'competition_branch',
        'competition_start_date',
        'competition_end_date',
        'description',
        'image',
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
        'image' => Blob::class,
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

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function competitionScale(): BelongsTo
    {
        return $this->belongsTo(CompetitionScale::class);
    }

    public function competitionTimeRange(): BelongsTo
    {
        return $this->belongsTo(CompetitionTimeRange::class);
    }

    public function competitionOutput(): BelongsTo
    {
        return $this->belongsTo(CompetitionOutput::class);
    }

    public function competitionRank(): BelongsTo
    {
        return $this->belongsTo(CompetitionRank::class);
    }
}
