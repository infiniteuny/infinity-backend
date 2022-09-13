<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class LeaderboardController extends Controller
{
    public function index()
    {
        $members = Member::has('teams')->with('teams.achievements')->get();
        foreach ($members as $member) {
            foreach ($member->teams as $team) {
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
        }

        $members = $members->sortByDesc(function ($member, $key) {
            return $member['points'] . $member['achievement_count'];
        });

        return view('leaderboard')->with([
            'config' => $this->config(),
            'members' => $members,
        ]);
    }

    public function detail($member_id)
    {
        $member = Member::with('teams.achievements')->find(Crypt::decryptString($member_id));
        foreach ($member->teams as $team) {
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

        return view('leaderboard-detail')->with([
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
