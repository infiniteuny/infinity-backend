<?php

namespace App\Jobs;

use App\Models\AchievementLeaderboard;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class SyncAchievementLeaderboards implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

        /**
     * Create a new job instance.
     */
    public function __construct(
        public int $year,
    ) {}

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->year;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $achievementLeaderboards = DB::table('achievements as a')
            ->select([
                'users.id as user_id',
                DB::raw('EXTRACT(YEAR FROM a.competition_start_date) as year'),
                DB::raw('SUM(COALESCE(cot.weight,0) * COALESCE(ctt.weight,0) * COALESCE(cs.weight,0) * COALESCE(ctr.weight,0) * COALESCE(co.weight,0) * COALESCE(cr.weight,0)) as total_points'),
            ])
            ->join('competitions as c', 'a.competition_id', '=', 'c.id')
            ->leftJoin('competition_organizer_types as cot', 'c.organizer_type_id', '=', 'cot.id')
            ->leftJoin('competition_team_types as ctt', 'a.competition_team_type_id', '=', 'ctt.id')
            ->leftJoin('competition_scales as cs', 'a.competition_scale_id', '=', 'cs.id')
            ->leftJoin('competition_time_ranges as ctr', 'a.competition_time_range_id', '=', 'ctr.id')
            ->leftJoin('competition_outputs as co', 'a.competition_output_id', '=', 'co.id')
            ->leftJoin('competition_ranks as cr', 'a.competition_rank_id', '=', 'cr.id')
            ->join('teams as t', 'a.team_id', '=', 't.id')
            ->join('team_members as tm', 't.id', '=', 'tm.team_id')
            ->join('users', 'tm.user_id', '=', 'users.id')
            ->groupBy('users.id', DB::raw('EXTRACT(YEAR FROM a.competition_start_date)'))
            ->whereYear('a.competition_start_date', $this->year)
            ->get();

        AchievementLeaderboard::where('year', now()->year)->whereNotIn('user_id', $achievementLeaderboards->pluck('user_id'))
            ->delete();

        foreach ($achievementLeaderboards as $row) {
            AchievementLeaderboard::updateOrCreate(
                [
                    'user_id' => $row->user_id,
                    'year' => (int) $row->year,
                ],
                [
                    'total_points' => $row->total_points,
                ]
            );
        }
    }
}
