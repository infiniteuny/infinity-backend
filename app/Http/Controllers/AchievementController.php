<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Http\Requests\StoreAchievementRequest;
use App\Http\Requests\UpdateAchievementRequest;
use App\Models\CompetitionLevel;
use App\Models\CompetitionOutput;
use App\Models\CompetitionRank;
use App\Models\CompetitionScale;
use App\Models\CompetitionTimeRange;
use App\Models\CompetitionType;
use App\Models\Team;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AchievementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $achievement = Achievement::with([
                'teams.members',
                'competitionTypes',
                'competitionScales',
                'competitionOutputs',
                'competitionTimeRanges',
                'competitionRanks',
                'competitionLevels'
            ])->orderBy('date', 'desc')->get();
            $data = $achievement->map(function ($achievement) {
                return [
                    'id' => Crypt::encryptString($achievement->id),
                    'team_name' => $achievement->teams->name,
                    'competition_name' => $achievement->competition_name,
                    'organizer' => $achievement->organizer,
                    'description' => $achievement->description,
                    'date' => Carbon::parse($achievement->date)->format('d M Y'),
                    'member' => $achievement->teams->members->map(function ($member) {
                        return [
                            'name' => $member->name,
                            'role' => $member->pivot->role,
                        ];
                    }),
                    'competition_type' => $achievement->competitionTypes->name,
                    'competition_scale' => $achievement->competitionScales->name,
                    'competition_output' => $achievement->competitionOutputs->name,
                    'competition_time_range' => $achievement->competitionTimeRanges->name,
                    'competition_rank' => $achievement->competitionRanks->name,
                    'competition_level' => $achievement->competitionLevels->name,
                    'point' => $achievement->competitionTypes->weight *
                        $achievement->competitionScales->weight *
                        $achievement->competitionOutputs->weight *
                        $achievement->competitionTimeRanges->weight *
                        $achievement->competitionRanks->weight *
                        $achievement->competitionLevels->weight . ' pts'
                ];
            });
            return DataTables::of($data)->addIndexColumn()
                ->addIndexColumn()
                ->make(true);
        }
        $data['competition_types'] = CompetitionType::all();
        $data['competition_scales'] = CompetitionScale::all();
        $data['competition_outputs'] = CompetitionOutput::all();
        $data['competition_time_ranges'] = CompetitionTimeRange::all();
        $data['competition_ranks'] = CompetitionRank::all();
        $data['competition_levels'] = CompetitionLevel::all();
        return view('admin.achievement.index')->with([
            'data' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAchievementRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAchievementRequest $request)
    {
        $validate = Validator::make($request->all(), [
            'team_name' => 'required|string|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'competition_name' => 'required|string|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'competition_organizer' => 'required|string|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'leader' => 'required|string',
            'member' => 'array',
            'competition_rank' => 'required|string',
            'competition_type' => 'required|string',
            'competition_scale' => 'required|string',
            'competition_outputs' => 'required|string',
            'competition_time_range' => 'required|string',
            'competition_level' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validate->fails()) {
            $error = $validate->errors()->all(':message');
            return redirect()->back()->with('error', implode(' ', $error))->withInput();
        }

        $request->merge([
            'leader' => Crypt::decryptString($request->leader),
        ]);

        if ($request->has('member')) {
            $members = $request->member;
            foreach ($members as $item => $value) {
                $members[$item] = Crypt::decryptString($value);
            }
            $request->merge([
                'member' => $members
            ]);
        }

        try {
            DB::transaction(function () use ($request) {
                $team = Team::create([
                    'name' => $request->team_name,
                ]);

                $team->members()->attach($request->leader, ['role' => 'leader']);

                if ($request->has('member')) {
                    foreach ($request->member as $member) {
                        $team->members()->attach($member, ['role' => 'member']);
                    }
                }

                $team->achievements()->create([
                    'competition_name' => $request->competition_name,
                    'organizer' => $request->competition_organizer,
                    'description' => $request->description,
                    'date' => $request->date,
                    'competition_type_id' => $request->competition_type,
                    'competition_scale_id' => $request->competition_scale,
                    'competition_output_id' => $request->competition_outputs,
                    'competition_time_range_id' => $request->competition_time_range,
                    'competition_rank_id' => $request->competition_rank,
                    'competition_level_id' => $request->competition_level,
                    'image' => $request->file('image')->store('public/images/achievement'),
                ]);
            });

            return redirect()->back()->with('success', 'Data berhasil disimpan');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Data gagal disimpan')->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Achievement  $achievement
     * @return \Illuminate\Http\Response
     */
    public function show(Achievement $achievement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Achievement  $achievement
     * @return \Illuminate\Http\Response
     */
    public function edit($achievement)
    {
        $achievement = Achievement::where('id', Crypt::decryptString($achievement))->with([
            'teams.members',
            'competitionTypes',
            'competitionScales',
            'competitionOutputs',
            'competitionTimeRanges',
            'competitionRanks',
            'competitionLevels'
        ])->first();

        $data['achievement'] = (object) [
            'id' => Crypt::encryptString($achievement->id),
            'team_name' => $achievement->teams->name,
            'competition_name' => $achievement->competition_name,
            'organizer' => $achievement->organizer,
            'description' => $achievement->description,
            'date' => $achievement->date,
            'image' => Storage::url($achievement->image),
            'competition_type' => $achievement->competitionTypes->name,
            'competition_scale' => $achievement->competitionScales->name,
            'competition_output' => $achievement->competitionOutputs->name,
            'competition_time_range' => $achievement->competitionTimeRanges->name,
            'competition_rank' => $achievement->competitionRanks->name,
            'competition_level' => $achievement->competitionLevels->name,
            'competition_type_id' => $achievement->competitionTypes->id,
            'competition_scale_id' => $achievement->competitionScales->id,
            'competition_output_id' => $achievement->competitionOutputs->id,
            'competition_time_range_id' => $achievement->competitionTimeRanges->id,
            'competition_rank_id' => $achievement->competitionRanks->id,
            'competition_level_id' => $achievement->competitionLevels->id,
            'point' => $achievement->competitionTypes->weight *
                $achievement->competitionScales->weight *
                $achievement->competitionOutputs->weight *
                $achievement->competitionTimeRanges->weight *
                $achievement->competitionRanks->weight *
                $achievement->competitionLevels->weight . ' pts'
        ];

        $data['competition_types'] = CompetitionType::all();
        $data['competition_scales'] = CompetitionScale::all();
        $data['competition_outputs'] = CompetitionOutput::all();
        $data['competition_time_ranges'] = CompetitionTimeRange::all();
        $data['competition_ranks'] = CompetitionRank::all();
        $data['competition_levels'] = CompetitionLevel::all();

        return view('admin.achievement.edit')->with('data', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAchievementRequest  $request
     * @param  \App\Models\Achievement  $achievement
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAchievementRequest $request, $achievement)
    {
        $validate = Validator::make($request->all(), [
            'team_name' => 'required|string|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'competition_name' => 'required|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'competition_organizer' => 'required|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
            'leader' => 'string',
            'member' => 'array',
            'competition_rank' => 'required|integer',
            'competition_type' => 'required|integer',
            'competition_scale' => 'required|integer',
            'competition_outputs' => 'required|integer',
            'competition_time_range' => 'required|integer',
            'competition_level' => 'required|integer',
            'image' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validate->fails()) {
            $error = $validate->errors()->all(':message');
            return redirect()->back()->with('error', implode(' ', $error));
        }


        try {
            DB::transaction(function () use ($request, $achievement) {
                $achievement = Achievement::where('id', Crypt::decryptString($achievement))->first();

                $team = $achievement->teams->update([
                    'name' => $request->team_name,
                ]);

                if ($request->has('leader')) {
                    $leader = $achievement->teams->members()->where('role', 'leader')->first()->id;
                    $achievement->teams->members()->detach(Crypt::decryptString($leader));
                    $achievement->teams->members()->attach($leader, ['role' => 'leader']);
                }

                if ($request->has('member')) {
                    $members = $achievement->teams->members()->where('role', 'member')->get();
                    foreach ($members as $member) {
                        $achievement->teams->members()->detach(Crypt::decryptString($member->id));
                        $achievement->teams->members()->attach($member->id, ['role' => 'member']);
                    }
                }

                if ($request->hasFile('image')) {
                    Storage::delete($achievement->image);
                    $achievement->competition_name = $request->competition_name;
                    $achievement->organizer = $request->competition_organizer;
                    $achievement->description = $request->description;
                    $achievement->date = $request->date;
                    $achievement->competition_type_id = $request->competition_type;
                    $achievement->competition_scale_id = $request->competition_scale;
                    $achievement->competition_output_id = $request->competition_outputs;
                    $achievement->competition_time_range_id = $request->competition_time_range;
                    $achievement->competition_rank_id = $request->competition_rank;
                    $achievement->competition_level_id = $request->competition_level;
                    $achievement->image = $request->file('image')->store('public/images/achievement');
                    $achievement->save();
                } else {
                    $achievement->competition_name = $request->competition_name;
                    $achievement->organizer = $request->competition_organizer;
                    $achievement->description = $request->description;
                    $achievement->date = $request->date;
                    $achievement->competition_type_id = $request->competition_type;
                    $achievement->competition_scale_id = $request->competition_scale;
                    $achievement->competition_output_id = $request->competition_outputs;
                    $achievement->competition_time_range_id = $request->competition_time_range;
                    $achievement->competition_rank_id = $request->competition_rank;
                    $achievement->competition_level_id = $request->competition_level;
                    $achievement->save();
                }
            });

            return redirect()->back()->with('success', 'Data berhasil diubah');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Data gagal diubah');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Achievement  $achievement
     * @return \Illuminate\Http\Response
     */
    public function destroy($achievement)
    {
        $achievement = Achievement::find(Crypt::decryptString($achievement));
        if ($achievement) {
            Storage::delete($achievement->image);
            $achievement->teams()->delete();
            return redirect()->back()->with('success', 'Data berhasil dihapus');
        } else {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }
    }
}
