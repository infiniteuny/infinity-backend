<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Analytics;
use App\Models\Achievement;
use App\Models\CompetitionLevel;
use App\Models\CompetitionRank;
use App\Models\CompetitionScale;
use App\Models\Config;
use App\Models\Faculty;
use App\Models\Freepik;
use App\Models\FundApplication;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Analytics\Period;

class DashboardController extends Controller
{
    public function adminDashboard()
    {

        $data['competition_scales'] = CompetitionScale::select('competition_scales.name', DB::raw('COUNT(achievements.competition_scale_id) as count'))
            ->leftJoin('achievements', 'competition_scales.id', '=', 'achievements.competition_scale_id')
            ->whereYear('achievements.date', '=', 2022)
            ->groupBy('competition_scales.name')
            ->orderBy('count', 'desc')
            ->get();

        $data['competition_ranks'] = CompetitionRank::select('competition_ranks.name', DB::raw('COUNT(achievements.competition_rank_id) as count'))
            ->leftJoin('achievements', 'competition_ranks.id', '=', 'achievements.competition_rank_id')
            ->whereYear('achievements.date', '=', 2022)
            ->groupBy('competition_ranks.name')
            ->orderBy('count', 'desc')
            ->get();

        $data['competition_levels'] = CompetitionLevel::select('competition_levels.name', DB::raw('COUNT(achievements.competition_level_id) as count'))
            ->leftJoin('achievements', 'competition_levels.id', '=', 'achievements.competition_level_id')
            ->whereYear('achievements.date', '=', 2022)
            ->groupBy('competition_levels.name')
            ->orderBy('count', 'desc')
            ->get();

        $most_visited_data = Analytics::fetchMostVisitedPages(Period::days(7))->take(8);
        $visitors_and_page_data = Analytics::fetchVisitorsAndPageViews(Period::days(7))->take(10);
        $top_referrers = Analytics::fetchTopReferrers(Period::days(7))->take(5);
        $user_types = Analytics::fetchUserTypes(Period::days(7))->take(10);
        $analitics['most_visited_url'] = json_encode($most_visited_data->pluck('url')->map(function ($url) {
            return substr($url, 0, 20);
        }));
        $analitics['most_visited_pageViews'] = json_encode($most_visited_data->pluck('pageViews'));
        $analitics['visitors_and_page_date'] = json_encode($visitors_and_page_data->map(function ($date) {
            return Carbon::parse($date['date'])->format('d M Y');
        }));
        $analitics['visitors_and_page_visitors'] = json_encode($visitors_and_page_data->pluck('visitors'));
        $analitics['visitors_and_page_pageViews'] = json_encode($visitors_and_page_data->pluck('pageViews'));
        $analitics['top_referrers_url'] = json_encode($top_referrers->pluck('url'));
        $analitics['top_referrers_page_view'] = json_encode($top_referrers->pluck('pageViews'));
        $analitics['user_type'] = json_encode($user_types->pluck('type'));
        $analitics['user_type_sessions'] = json_encode($user_types->pluck('sessions'));

        $data['achievements'] = Achievement::whereYear('date', Carbon::now()->year)->orderBy('date')->count();
        $data['products'] = count(json_decode(Http::get(config('app.api_url') . '/api/galleries')->body())->data);
        $data['events'] = count(json_decode(Http::get(config('app.api_url') . '/api/events')->body())->data);
        $data['members'] = Member::where('status', true)->count() . ' / ' . Member::count();

        $faculties = Faculty::withCount('members')->having('members_count', '>', 0)->orderBy('members_count', 'desc')->get();
        $data['faculty_name'] = collect(json_decode($faculties->pluck('name')))->map(function ($name) {
            $name = str_replace(' dan', '', $name);
            $faculty = explode(' ', $name);
            $first_letters = array_map(function ($word) {
                return $word[0];
            }, $faculty);
            return 'F' . implode('', $first_letters);
        });
        $data['faculty_name'] = json_encode($data['faculty_name']);
        $data['faculty_member_count'] = json_encode($faculties->pluck('members_count'));

        $data['achievements_graph'] = Achievement::selectRaw('count(*) as count, year(date) as year')
            ->groupBy(DB::raw('year(date)'))
            ->orderBy('date')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'count' => $item->count,
                ];
            });
        $data['achievements_graph_year'] = json_encode($data['achievements_graph']->pluck('year'));
        $data['achievements_graph_count'] = json_encode($data['achievements_graph']->pluck('count'));

        $data['users'] = User::orderBy('created_at', 'desc')->take(6)->get();
        $data['users'] = $data['users']->map(function ($user) {
            return [
                'name' => $user->name,
                'is_verified' => $user->email_verified_at ? true : false,
                'is_verified_text' => $user->email_verified_at ? 'Email Terverifikasi' : 'Email Belum Terverifikasi',
                'avatar' => $user->avatar,
                'created_at_date' => Carbon::parse($user->created_at)->format('d M'),
                'created_at_hour' => Carbon::parse($user->created_at)->format('H:i'),
            ];
        });

        $data['freepik_leaderboard'] = User::whereHas('freepikDownloads')
            ->with(['freepikDownloads' => function ($query) {
                $query->withCount('freepiks');
            }, 'members.programStudies.faculties'])->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'student_id' => $user->student_id,
                    'program_study' => $user->members->programStudies->name,
                    'faculty' => $user->members->programStudies->faculties->name,
                    'freepik_count' => $user->freepikDownloads->freepiks_count,
                ];
            })->sortByDesc('freepik_count')->take(10);

        $data['configs'] = Config::all()->mapWithKeys(function ($item) {
            return [$item['key'] => $item['value']];
        });

        return view('admin.dashboard')->with([
            'analitics' => $analitics,
            'data' => $data,
        ]);
    }

    public function studentDashboard()
    {
        $user = Auth::user();
        $data['achievements'] = Achievement::whereRelation('teams.members', 'student_id', $user->student_id)->count();
        $data['freepik']['used'] = Freepik::whereRelation('freepikDownloads.users', 'student_id', $user->student_id)
            ->whereRelation('freepikDownloads.users', 'status', 'completed')
            ->whereDate('created_at', Carbon::today())
            ->count();
        $data['freepik']['quota'] = $user->freepikDownloads ? ($user->freepikDownloads->limit + $user->freepikDownloads->limit_addons) : 3;
        $data['fund_applications'] = FundApplication::where('user_id', Auth::id())->where(function ($query) {
            $query->where('status', 'waiting')
                ->orwhere('status', 'accepted');
        })->count();
        $data['membership_status'] = Member::where('student_id', $user->student_id)->first()->status ? 'Aktif' : 'Tidak Aktif';
        $data['freepik_leaderboard'] = User::whereHas('freepikDownloads')
            ->with(['freepikDownloads' => function ($query) {
                $query->withCount('freepiks');
            }, 'members.programStudies.faculties'])->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'student_id' => $user->student_id,
                    'program_study' => $user->members->programStudies->name,
                    'faculty' => $user->members->programStudies->faculties->name,
                    'freepik_count' => $user->freepikDownloads->freepiks_count,
                ];
            })->sortByDesc('freepik_count')->take(10);
        return view('student.dashboard')->with([
            'data' => $data,
        ]);
    }
}
