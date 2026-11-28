<?php

namespace App\Jobs;

use App\Models\AchievementLeaderboard;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;

class SyncAchievementLeaderboards implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 900;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $year,
    ) {}

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 5;

    /**
     * The number of seconds to wait before retrying an exception.
     */
    public int $backoff = 10;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return (string) $this->year;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("achievement-leaderboard:{$this->year}"))
                ->shared()
                ->releaseAfter(10)
                ->expireAfter(240),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $castType = DB::connection()->getDriverName() === 'pgsql' ? 'NUMERIC(38, 0)' : 'BIGINT';

        $achievementLeaderboards = DB::table('achievements AS a')
            ->select([
                'users.id AS user_id',
                DB::raw(
                    "SUM(CAST(COALESCE(cot.weight, 0) AS {$castType}) * CAST(COALESCE(ctt.weight, 0) AS {$castType}) * CAST(COALESCE(cs.weight, 0) AS {$castType}) * CAST(COALESCE(ctr.weight, 0) AS {$castType}) * CAST(COALESCE(co.weight, 0) AS {$castType}) * CAST(COALESCE(cr.weight, 0) AS {$castType})) AS total_points"
                ),
            ])
            ->join('competition_instances AS ci', 'a.competition_instance_id', '=', 'ci.id')
            ->join('teams AS t', 'a.team_id', '=', 't.id')
            ->join('team_members AS tm', 't.id', '=', 'tm.team_id')
            ->join('users', 'tm.user_id', '=', 'users.id')
            ->leftJoin('competition_organizer_types AS cot', 'ci.organizer_type_id', '=', 'cot.id')
            ->leftJoin('competition_team_types AS ctt', 't.team_type_id', '=', 'ctt.id')
            ->leftJoin('competition_scales AS cs', 'a.competition_scale_id', '=', 'cs.id')
            ->leftJoin('competition_time_ranges AS ctr', 'a.competition_time_range_id', '=', 'ctr.id')
            ->leftJoin('competition_outputs AS co', 'a.competition_output_id', '=', 'co.id')
            ->leftJoin('competition_ranks AS cr', 'a.competition_rank_id', '=', 'cr.id')
            ->where('a.status', 'ACCEPTED')
            ->whereYear('a.competition_start_date', $this->year)
            ->groupBy('users.id')
            ->get();

        DB::transaction(function () use ($achievementLeaderboards) {
            AchievementLeaderboard::where('year', $this->year)
                ->whereNotIn('user_id', $achievementLeaderboards->pluck('user_id'))
                ->delete();

            foreach ($achievementLeaderboards as $row) {
                AchievementLeaderboard::updateOrCreate(
                    [
                        'user_id' => $row->user_id,
                        'year' => $this->year,
                    ],
                    [
                        'total_points' => $this->clampTotalPoints($row->total_points),
                    ]
                );
            }
        });
    }

    private function clampTotalPoints(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return 0;
            }

            $intPart = explode('.', $trimmed)[0];
            if (preg_match('/^\+?\d+$/', $intPart) === 1) {
                $normalized = ltrim($intPart, '+0');
                if ($normalized === '') {
                    return 0;
                }

                $maxString = (string) PHP_INT_MAX;
                if (
                    strlen($normalized) > strlen($maxString)
                    || (strlen($normalized) === strlen($maxString) && strcmp($normalized, $maxString) >= 0)
                ) {
                    return PHP_INT_MAX;
                }

                return (int) $normalized;
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
}
