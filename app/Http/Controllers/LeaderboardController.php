<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Nette\Utils\Arrays;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('year')) {
            $members = Member::has('teams')
                ->whereRelation('teams.achievements', 'date', '>=', Carbon::parse($request->year . '-01-01'))
                ->whereRelation('teams.achievements', 'date', '<=', Carbon::parse($request->year . '-12-31'))
                ->whereRelation('teams.achievements', 'status', '=', 'accepted')
                ->with([
                    'teams' => function ($query) use ($request) {
                        $query->whereHas('achievements', function ($query) use ($request) {
                            $query->where('date', '>=', Carbon::parse($request->year . '-01-01'))
                                ->where('date', '<=', Carbon::parse($request->year . '-12-31'))
                                ->where('status', '=', 'accepted');
                        });
                    },
                    'teams.achievements.competitionScales',
                    'teams.achievements.competitionLevels',
                    'teams.achievements.competitionRanks',
                    'teams.achievements.competitionTimeRanges',
                    'teams.achievements.competitionOutputs',
                    'teams.achievements.competitionTypes',
                    'programStudies',
                ])
                ->withCount(['teams' => function ($query) use ($request) {
                    $query->whereRelation('achievements', 'date', '>=', Carbon::parse($request->year . '-01-01'))
                        ->whereRelation('achievements', 'date', '<=', Carbon::parse($request->year . '-12-31'))
                        ->whereRelation('achievements', 'status', '=', 'accepted');
                }])
                ->get()->toArray();
            $year = $request->year;
        } else {
            $members = Member::has('teams')
                ->whereRelation('teams.achievements', 'date', '>=', Carbon::parse(Carbon::now()->year . '-01-01'))
                ->whereRelation('teams.achievements', 'date', '<=', Carbon::parse(Carbon::now()->year . '-12-31'))
                ->whereRelation('teams.achievements', 'status', '=', 'accepted')
                ->with([
                    'teams' => function ($query) {
                        $query->whereHas('achievements', function ($query) {
                            $query->where('date', '>=', Carbon::parse(Carbon::now()->year . '-01-01'))
                                ->where('date', '<=', Carbon::parse(Carbon::now()->year . '-12-31'))
                                ->where('status', '=', 'accepted');
                        });
                    },
                    'teams.achievements.competitionScales',
                    'teams.achievements.competitionLevels',
                    'teams.achievements.competitionRanks',
                    'teams.achievements.competitionTimeRanges',
                    'teams.achievements.competitionOutputs',
                    'teams.achievements.competitionTypes',
                    'programStudies',
                ])
                ->withCount(['teams' => function ($query) {
                    $query->whereRelation('achievements', 'date', '>=', Carbon::parse(Carbon::now()->year . '-01-01'))
                        ->whereRelation('achievements', 'date', '<=', Carbon::parse(Carbon::now()->year . '-12-31'))
                        ->whereRelation('achievements', 'status', '=', 'accepted');
                }])
                ->get()->toArray();
            $year = Carbon::now()->year;
        }

        $anggota = [];
        collect($members)->map(function ($member) use (&$anggota) {
            $member['achievement_count'] = $member['teams_count'];
            $member['points'] = collect($member['teams'])->map(function ($team) {
                $team['point'] = $team['achievements']['competition_scales']['weight'] *
                    $team['achievements']['competition_levels']['weight'] *
                    $team['achievements']['competition_ranks']['weight'] *
                    $team['achievements']['competition_time_ranges']['weight'] *
                    $team['achievements']['competition_outputs']['weight'] *
                    $team['achievements']['competition_types']['weight'];
                return $team;
            })->sum('point');
            array_push($anggota, $member);
        });
        $anggota = collect($anggota)->sortBy('name')->sortByDesc('achievement_count')->sortByDesc('points')->values()->toArray();

        $yearSelect = Achievement::selectRaw('YEAR(date) as year')->distinct()->get()->toArray();
        $yearSelect = collect($yearSelect)->flatten()->toArray();

        return view('landing.leaderboard')->with([
            'config' => $this->config(),
            'members' => $anggota,
            'year' => $year,
            'yearSelect' => $yearSelect,
        ]);
    }

    public function detail($member_id)
    {
        $member = Member::with([
            'teams' => function ($query) {
                $query->whereRelation('achievements', 'status', '=', 'accepted');
            },
            'teams.achievements.competitionScales',
            'teams.achievements.competitionLevels',
            'teams.achievements.competitionRanks',
            'teams.achievements.competitionOutputs',
            'teams.achievements.competitionTimeRanges',
            'teams.achievements.competitionTypes',
        ])->find(Crypt::decryptString($member_id));
        foreach ($member['teams'] as $team) {
            $team->points =
                $team->achievements->competitionScales->weight *
                $team->achievements->competitionLevels->weight *
                $team->achievements->competitionRanks->weight *
                $team->achievements->competitionOutputs->weight *
                $team->achievements->competitionTimeRanges->weight *
                $team->achievements->competitionTypes->weight;
        }
        $member->points = $member->teams->sum('points');
        $member->achievement_count = $member->teams->count();

        $name = $member->name;
        $member = $member->teams->sortByDesc('achievements.date');

        return view('landing.leaderboard-detail')->with([
            'config' => $this->config(),
            'member' => $member,
            'name' => $name,
        ]);
    }

    public static function config()
    {
        $response = Http::get(config('app.api_url') . '/api/configs');
        $config = [];
        foreach (json_decode($response)->data as $item) {
            $config[$item->attributes->name] = $item->attributes->value;
        }
        return $config;
    }
}
