<?php

namespace App\Models;

use App\Casts\Blob;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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
        'competition_instance_id',
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'point',
    ];

    protected function point(): Attribute
    {
        return new Attribute(
            get: function () {
                if (! $this->getKey()) {
                    return 0;
                }

                return (int) (DB::table('achievements as a')
                    ->leftJoin('competition_instances as ci', 'ci.id', '=', 'a.competition_instance_id')
                    ->leftJoin('competition_organizer_types as cot', 'cot.id', '=', 'ci.organizer_type_id')
                    ->leftJoin('teams as t', 't.id', '=', 'a.team_id')
                    ->leftJoin('competition_team_types as ctt', 'ctt.id', '=', 't.team_type_id')
                    ->leftJoin('competition_scales as cs', 'cs.id', '=', 'a.competition_scale_id')
                    ->leftJoin('competition_time_ranges as ctr', 'ctr.id', '=', 'a.competition_time_range_id')
                    ->leftJoin('competition_outputs as co', 'co.id', '=', 'a.competition_output_id')
                    ->leftJoin('competition_ranks as cr', 'cr.id', '=', 'a.competition_rank_id')
                    ->where('a.id', $this->getKey())
                    ->selectRaw('COALESCE(cot.weight, 0) * COALESCE(ctt.weight, 0) * COALESCE(cs.weight, 0) * COALESCE(ctr.weight, 0) * COALESCE(co.weight, 0) * COALESCE(cr.weight, 0) as point')
                    ->value('point') ?? 0);
            },
        );
    }

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
