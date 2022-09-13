<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    public function teams()
    {
        return $this->belongsTo(Team::class);
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
