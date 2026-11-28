<?php

namespace App\Models;

use App\Casts\Blob;
use App\Casts\DateOnly;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
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
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'computed_point',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'competition_start_date' => DateOnly::class,
        'competition_end_date' => DateOnly::class,
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

    public static function pointProductSql(
        string $cot = 'cot',
        string $ctt = 'ctt',
        string $cs = 'cs',
        string $ctr = 'ctr',
        string $co = 'co',
        string $cr = 'cr',
    ): string {
        $type = DB::getFacadeRoot() !== null && DB::connection()->getDriverName() === 'pgsql'
            ? 'NUMERIC(38, 0)'
            : 'BIGINT';

        return "CAST(COALESCE({$cot}.weight, 0) AS {$type})"
            ." * CAST(COALESCE({$ctt}.weight, 0) AS {$type})"
            ." * CAST(COALESCE({$cs}.weight, 0) AS {$type})"
            ." * CAST(COALESCE({$ctr}.weight, 0) AS {$type})"
            ." * CAST(COALESCE({$co}.weight, 0) AS {$type})"
            ." * CAST(COALESCE({$cr}.weight, 0) AS {$type})";
    }

    public function scopeWithComputedPoint(Builder $query): Builder
    {
        if ($query->getQuery()->columns === null) {
            $query->select($this->qualifyColumn('*'));
        }

        $subquery = DB::table('achievements as a_pt')
            ->leftJoin('competition_instances as ci_pt', 'ci_pt.id', '=', 'a_pt.competition_instance_id')
            ->leftJoin('competition_organizer_types as cot_pt', 'cot_pt.id', '=', 'ci_pt.organizer_type_id')
            ->leftJoin('teams as t_pt', 't_pt.id', '=', 'a_pt.team_id')
            ->leftJoin('competition_team_types as ctt_pt', 'ctt_pt.id', '=', 't_pt.team_type_id')
            ->leftJoin('competition_scales as cs_pt', 'cs_pt.id', '=', 'a_pt.competition_scale_id')
            ->leftJoin('competition_time_ranges as ctr_pt', 'ctr_pt.id', '=', 'a_pt.competition_time_range_id')
            ->leftJoin('competition_outputs as co_pt', 'co_pt.id', '=', 'a_pt.competition_output_id')
            ->leftJoin('competition_ranks as cr_pt', 'cr_pt.id', '=', 'a_pt.competition_rank_id')
            ->whereColumn('a_pt.id', $this->qualifyColumn('id'))
            ->selectRaw(self::pointProductSql('cot_pt', 'ctt_pt', 'cs_pt', 'ctr_pt', 'co_pt', 'cr_pt'));

        return $query->selectSub($subquery, 'computed_point');
    }

    protected function point(): Attribute
    {
        return new Attribute(
            get: function (): int {
                if (array_key_exists('computed_point', $this->attributes)) {
                    return self::normalizePointValue($this->attributes['computed_point']);
                }

                if (! $this->getKey()) {
                    return 0;
                }

                $value = DB::table('achievements as a')
                    ->leftJoin('competition_instances as ci', 'ci.id', '=', 'a.competition_instance_id')
                    ->leftJoin('competition_organizer_types as cot', 'cot.id', '=', 'ci.organizer_type_id')
                    ->leftJoin('teams as t', 't.id', '=', 'a.team_id')
                    ->leftJoin('competition_team_types as ctt', 'ctt.id', '=', 't.team_type_id')
                    ->leftJoin('competition_scales as cs', 'cs.id', '=', 'a.competition_scale_id')
                    ->leftJoin('competition_time_ranges as ctr', 'ctr.id', '=', 'a.competition_time_range_id')
                    ->leftJoin('competition_outputs as co', 'co.id', '=', 'a.competition_output_id')
                    ->leftJoin('competition_ranks as cr', 'cr.id', '=', 'a.competition_rank_id')
                    ->where('a.id', $this->getKey())
                    ->selectRaw(self::pointProductSql().' as point')
                    ->value('point');

                return self::normalizePointValue($value);
            },
        );
    }

    private static function normalizePointValue(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if ($value === null) {
            return 0;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || ! is_numeric($trimmed)) {
                return 0;
            }

            $intValue = filter_var($trimmed, FILTER_VALIDATE_INT);
            if ($intValue !== false) {
                return $intValue;
            }

            $value = (float) $trimmed;
        }

        if (is_float($value)) {
            if (is_nan($value)) {
                return 0;
            }

            if ($value >= (float) PHP_INT_MAX) {
                return PHP_INT_MAX;
            }

            if ($value <= (float) PHP_INT_MIN) {
                return PHP_INT_MIN;
            }

            return (int) $value;
        }

        return 0;
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
