<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'competition_id',
        'competition_team_type_id',
        'competition_scale_id',
        'competition_time_range_id',
        'competition_output_id',
        'competition_rank_id',
        'competition_branch',
        'competition_date',
        'description',
        'image',
        'status',
    ];

    protected $dateFormat = DATE_ATOM;

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function teams()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public function competitionScales()
    {
        return $this->belongsTo(CompetitionScale::class, 'competition_scale_id', 'id');
    }

    public function competitionRanks()
    {
        return $this->belongsTo(CompetitionRank::class, 'competition_rank_id', 'id');
    }

    public function competitionOutputs()
    {
        return $this->belongsTo(CompetitionOutput::class, 'competition_output_id', 'id');
    }

    public function competitionTimeRanges()
    {
        return $this->belongsTo(CompetitionTimeRange::class, 'competition_time_range_id', 'id');
    }
}
