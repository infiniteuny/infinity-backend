<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'competition_type_id',
        'competition_scale_id',
        'competition_output_id',
        'competition_time_range_id',
        'competition_rank_id',
        'competition_level_id',
        'competition_name',
        'organizer',
        'date',
        'description',
        'image',
        'status',
    ];

    public function teams()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public function competitionScales()
    {
        return $this->belongsTo(CompetitionScale::class, 'competition_scale_id', 'id');
    }

    public function competitionLevels()
    {
        return $this->belongsTo(CompetitionLevel::class, 'competition_level_id', 'id');
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

    public function competitionTypes()
    {
        return $this->belongsTo(CompetitionType::class, 'competition_type_id', 'id');
    }
}
