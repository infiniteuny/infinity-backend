<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFundApplicationRequest;
use App\Http\Requests\UpdateFundApplicationRequest;
use App\Models\FundApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class FundApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (Auth::user()->hasRole('admin')) {
            $fund = FundApplication::orderBy('updated_at', 'desc')->get();
        } else {
            $fund = FundApplication::where('user_id', auth()->user()->id)->orderBy('updated_at', 'desc')->get();
        }

        $data = $fund->map(function ($fund) {

            $leader = json_decode($fund->team_leader);
            $leader->role = 'leader';
            if (collect(json_decode($fund->team_members))->count() > 1) {
                $member = collect(json_decode($fund->team_members))->map(function ($member) {
                    $member->role = 'member';

                    return $member;
                });
                $member->push($leader);
            } else {
                $member[0] = collect($leader);
            }
            $member = collect($member)->sortBy('role')->values()->all();

            return [
                'id' => Crypt::encryptString($fund->id),
                'competition_name' => $fund->competition_name,
                'competition_url' => $fund->competition_url,
                'competition_date' => Carbon::parse($fund->competition_date)->format('d M Y'),
                'competition_branch' => $fund->competition_branch,
                'team_name' => $fund->team_name,
                'team_members' => $member,
                'status' => $fund->status,
                'student_id_card' => $fund->student_id_card,
                'letter_of_acceptance' => $fund->letter_of_acceptance,
                'budget_plan' => $fund->budget_plan,
            ];
        });

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
        if (Auth::user()->hasRole('admin')) {
            return view('admin.fund_application.index');
        } else {
            return view('student.fund_application.index');
        }
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
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFundApplicationRequest $request)
    {
        $validate = Validator::make($request->all(), [
            'competition_name' => 'required|string|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'competition_url' => 'required|url',
            'competition_date' => 'required|date',
            'competition_branch' => 'required|string',
            'team_name' => 'required|string',
            'team_leader' => 'required|array',
            'team_member' => 'array',
            'student_id_card' => 'required|file|mimes:pdf|max:2048',
            'loa' => 'required|file|mimes:pdf|max:2048',
            'budget_plan' => 'required|file|mimes:pdf|max:2048',
        ]);

        if ($validate->fails()) {
            $error = $validate->errors()->all(':message');

            return redirect()->back()->with('error', implode(' ', $error))->withInput();
        }

        $team_leader = json_encode([
            'name' => $request->team_leader['name'],
            'student_id' => $request->team_leader['student_id'],
            'phone' => $this->indonesianPhoneFormat($request->team_leader['phone']),
        ]);

        try {
            DB::transaction(function () use ($request, $team_leader) {
                $fund = new FundApplication();
                $fund->user_id = Auth::user()->id;
                $fund->competition_name = $request->competition_name;
                $fund->competition_url = $request->competition_url;
                $fund->competition_date = $request->competition_date;
                $fund->competition_branch = $request->competition_branch;
                $fund->team_name = $request->team_name;
                $fund->team_leader = $team_leader;
                $fund->team_members = $request->has('team_member') ? json_encode($this->parseMember($request->team_member)) : '[{}]';
                $fund->student_id_card = $request->file('student_id_card')->store('public/documents/fund_application/student_id_card', 'public');
                $fund->letter_of_acceptance = $request->file('loa')->store('public/documents/fund_application/letter_of_acceptance', 'public');
                $fund->budget_plan = $request->file('budget_plan')->store('public/documents/fund_application/budget_plan', 'public');
                $fund->save();
            });

            return redirect()->back()->with('success', 'Yeyy! pengajuan berhasil dibuat');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Waduh gagal, coba lagi nanti ya!')->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(FundApplication $fundApplication)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FundApplication  $fundApplication
     * @return \Illuminate\Http\Response
     */
    public function edit($fundApplication)
    {
        $data['fund'] = FundApplication::find(Crypt::decryptString($fundApplication));
        $data['fund']->id = $data['fund']->id;
        $data['fund']->team_leader = collect(json_decode($data['fund']->team_leader));
        $data['fund']->team_members = $data['fund']->team_members == '[{}]' ? null :
            collect(json_decode($data['fund']->team_members))->values()->all();
        $data['fund']->team_members_count = $data['fund']->team_members == null ? 0 : count($data['fund']->team_members);
        $data['fund']->student_id_card = Storage::url($data['fund']->student_id_card);
        $data['fund']->letter_of_acceptance = Storage::url($data['fund']->letter_of_acceptance);
        $data['fund']->budget_plan = Storage::url($data['fund']->budget_plan);
        if (Auth::user()->hasRole('admin')) {
            return view('admin.fund_application.edit')->with(['data' => $data]);
        } else {
            return view('student.fund_application.edit')->with(['data' => $data]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\FundApplication  $fundApplication
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFundApplicationRequest $request, $fundApplication)
    {
        $validate = Validator::make($request->all(), [
            'competition_name' => 'required|string|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'competition_url' => 'required|url',
            'competition_date' => 'required|date',
            'competition_branch' => 'required|string',
            'team_name' => 'required|string',
            'team_leader' => 'required|array',
            'team_member' => 'array',
            'student_id_card' => 'file|mimes:pdf|max:2048',
            'loa' => 'file|mimes:pdf|max:2048',
            'budget_plan' => 'file|mimes:pdf|max:2048',
            'status' => 'in:waiting,accepted,rejected',
        ]);

        if ($validate->fails()) {
            $error = $validate->errors()->all(':message');

            return redirect()->back()->with('error', implode(' ', $error))->withInput();
        }

        $team_leader = json_encode([
            'name' => $request->team_leader['name'],
            'student_id' => $request->team_leader['student_id'],
            'phone' => $this->indonesianPhoneFormat($request->team_leader['phone']),
        ]);

        try {
            DB::transaction(function () use ($request, $fundApplication, $team_leader) {
                $fund = FundApplication::find(Crypt::decryptString($fundApplication));
                $fund->competition_name = $request->competition_name;
                $fund->competition_url = $request->competition_url;
                $fund->competition_date = $request->competition_date;
                $fund->competition_branch = $request->competition_branch;
                $fund->team_name = $request->team_name;
                $fund->team_leader = $team_leader;
                $fund->team_members = $request->has('team_member') ? json_encode($this->parseMember($request->team_member)) : '[{}]';
                if ($request->hasFile('student_id_card')) {
                    Storage::delete($fund->student_id_card);
                    $fund->student_id_card = $request->file('student_id_card')->store('public/documents/fund_application/student_id_card', 'public');
                }
                if ($request->hasFile('loa')) {
                    Storage::delete($fund->letter_of_acceptance);
                    $fund->letter_of_acceptance = $request->file('loa')->store('public/documents/fund_application/letter_of_acceptance', 'public');
                }
                if ($request->hasFile('budget_plan')) {
                    Storage::delete($fund->budget_plan);
                    $fund->budget_plan = $request->file('budget_plan')->store('public/documents/fund_application/budget_plan', 'public');
                }
                if ($request->has('status')) {
                    $fund->status = $request->status;
                }
                $fund->save();
            });

            return redirect()->back()->with('success', 'Yeyy! pengajuan berhasil diubah');
        } catch (\Throwable $th) {
            return $th;

            return redirect()->back()->with('error', 'Waduh gagal, coba lagi nanti ya!')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FundApplication  $fundApplication
     * @return \Illuminate\Http\Response
     */
    public function destroy($fundApplication)
    {
        $fund = FundApplication::find(Crypt::decryptString($fundApplication));
        if ($fund->exists()) {
            if (Storage::exists($fund->student_id_card)) {
                Storage::delete($fund->student_id_card);
            }
            if (Storage::exists($fund->letter_of_acceptance)) {
                Storage::delete($fund->letter_of_acceptance);
            }
            if (Storage::exists($fund->budget_plan)) {
                Storage::delete($fund->budget_plan);
            }
            $fund->delete();

            return redirect()->back()->with('success', 'Yeyy! pengajuan berhasil dihapus');
        } else {
            return redirect()->back()->with('error', 'Waduh gagal, coba lagi nanti ya!');
        }
    }

    public function downloadStudentIdCard($id)
    {
        $id = Crypt::decryptString($id);
        $fund = FundApplication::findOrFail($id);
        $member = $fund->users()->first()->members->name;
        if (Storage::exists($fund->student_id_card)) {
            return Storage::download($fund->student_id_card, $member.' - '.$fund->competition_name.' - scan_kta.pdf');
        } else {
            return redirect()->back()->with('error', 'Yah filenya ga ketemu');
        }
    }

    public function downloadLetterOfAcceptance($id)
    {
        $id = Crypt::decryptString($id);
        $fund = FundApplication::findOrFail($id);
        $member = $fund->users()->first()->members->name;
        if (Storage::exists($fund->letter_of_acceptance)) {
            return Storage::download($fund->letter_of_acceptance, $member.' - '.$fund->competition_name.' - letter_of_acceptance.pdf');
        } else {
            return redirect()->back()->with('error', 'Yah filenya ga ketemu');
        }
    }

    public function downloadBudgetPlan($id)
    {
        $id = Crypt::decryptString($id);
        $fund = FundApplication::findOrFail($id);
        $member = $fund->users()->first()->members->name;
        if (Storage::exists($fund->budget_plan)) {
            return Storage::download($fund->budget_plan, $member.' - '.$fund->competition_name.' - rencana_anggaran_biaya.pdf');
        } else {
            return redirect()->back()->with('error', 'Yah filenya ga ketemu');
        }
    }

    public function accept($id)
    {
        $id = Crypt::decryptString($id);
        $fund = FundApplication::findOrFail($id);
        $fund->status = 'accepted';
        $fund->save();

        return redirect()->back()->with('success', 'Yeyy! pengajuan berhasil diterima');
    }

    public function reject($id)
    {
        $id = Crypt::decryptString($id);
        $fund = FundApplication::findOrFail($id);
        $fund->status = 'rejected';
        $fund->save();

        return redirect()->back()->with('success', 'Yeyy! pengajuan berhasil ditolak');
    }

    public function indonesianPhoneFormat($phone)
    {
        $phone = trim($phone);
        $phone = strip_tags($phone);
        $phone = str_replace(' ', '', $phone);
        $phone = str_replace('(', '', $phone);
        $phone = str_replace('.', '', $phone);

        if (! preg_match('/[^+0-9]/', trim($phone))) {
            if (substr(trim($phone), 0, 3) == '+62') {
                $phone = trim($phone);
            } elseif (substr($phone, 0, 2) == '62') {
                $phone = substr($phone, 2);
            } elseif (substr($phone, 0, 1) == '0') {
                $phone = substr($phone, 1);
            }
        }

        return $phone;
    }

    public function parseMember($members)
    {
        $team_member_raw = collect($members);
        $team_member = [];
        foreach ($team_member_raw['student_id'] as $member => $value) {
            $team_member[] = [
                'name' => $team_member_raw['name'][$member][0],
                'student_id' => $team_member_raw['student_id'][$member][0],
                'phone' => $this->indonesianPhoneFormat($team_member_raw['phone'][$member][0]),
            ];
        }

        return $team_member;
    }
}
