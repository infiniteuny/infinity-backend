<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberBulkRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\Response
    {
        if ($request->ajax()) {
            $members = Member::with('programStudies.faculties', 'teams')->orderBy('name', 'asc')->get();
            $data = $members->map(function ($member) {
                $data = [];
                $data['id'] = Crypt::encryptString($member->id);
                $data['nama'] = $member->name;
                $data['nim'] = $member->student_id;
                $data['prodi'] = $member->programStudies->name;
                $data['fakultas'] = $member->programStudies->faculties->name;
                $data['jumlah_prestasi'] = $member->teams->count();
                $data['tanggal_aktif'] = $member->start_date;
                $data['tanggal_selesai'] = $member->end_date ? $member->end_date : '-';
                $data['alb'] = $member->is_extraordinary ? 'Ya' : 'Tidak';
                $data['status'] = $member->status ? true : false;

                return $data;
            });

            return DataTables::of($data)->addIndexColumn()
                ->addIndexColumn()
                ->make(true);
        }

        return view('admin.member.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request): \Illuminate\Http\Response
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'student_id' => 'required|integer',
            'programStudy' => 'required|string',
            'status' => 'required|boolean',
            'is_extraordinary' => 'required|boolean',
            'date_start' => 'required|string',
            'date_end' => 'required|string',
        ]);

        if ($validate->fails()) {
            $error = $validate->errors()->all(':message');

            return redirect()->back()->with('error', implode(' ', $error));
        }

        try {
            $member = new Member();
            $member->name = $request->name;
            $member->student_id = $request->student_id;
            $member->program_study_id = $request->programStudy;
            $member->status = $request->status;
            $member->is_extraordinary = $request->is_extraordinary;
            $member->start_date = $request->date_start;
            $member->end_date = $request->date_end;
            $member->save();

            return redirect()->back()->with('success', 'Berhasil menambahkan anggota');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Gagal menambahkan anggota');
        }
    }

    public function storeBulk(StoreMemberBulkRequest $request)
    {
        $validate = Validator::make($request->all(), [
            'members' => ['array'],
            'members.*.name' => ['string', 'required'],
            'members.*.student_id' => ['string', 'numeric', 'required'],
            'members.*.start_date' => ['date'],
            'members.*.end_date' => ['date'],
            'members.*.status' => ['integer'],
            'members.*.is_extraordinary' => ['integer'],
        ]);

        if ($validate->fails()) {
            $error = $validate->errors()->all(':message');

            return response(['error' => implode(' ', $error)], 400);
        }

        $successCount = 0;
        foreach ($request->members as $item) {
            try {
                $member = new Member();
                $member->name = $item['name'];
                $member->student_id = $item['student_id'];
                $member->program_study_id = 1;
                $member->status = $item['status'];
                $member->is_extraordinary = $item['is_extraordinary'];
                $member->start_date = $item['start_date'];
                $member->end_date = $item['end_date'];
                $member->save();
                $successCount++;
            } catch (\Throwable $th) {
                //                error_log($th);
            }
        }

        if ($successCount > 0) {
            return response(['message' => 'Berhasil menambahkan anggota', 'successCount' => $successCount], 201);
        }

        return response(['message' => 'Gagal menambahkan anggota', 'successCount' => $successCount], 500);
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member): \Illuminate\Http\Response
    {
        $member = Member::find(Crypt::decryptString($member));
        $roles = Role::all()->map(function ($role) {
            return (object) [
                'id' => Crypt::encryptString($role->id),
                'name' => $role->name,
            ];
        });
        $user = User::where('student_id', $member->student_id)->first();
        if ($user) {
            $user->roles = $user->getRoleNames()->first();
        }
        $member->avatar = $user ? $user->avatar : 'https://ui-avatars.com/api/?name='.$member->name.'&background=0D8ABC&color=fff';
        $member->email = $user ? $user->email : 'Belum buat akun';

        return view('admin.member.edit')->with([
            'member' => $member,
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member): \Illuminate\Http\Response
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'student_id' => 'required|integer',
            'programStudy' => 'required|string',
            'status' => 'required|boolean',
            'is_extraordinary' => 'required|boolean',
            'date_start' => 'required|string',
            'date_end' => 'required|string',
        ]);

        if ($validate->fails()) {
            $error = $validate->errors()->all(':message');

            return redirect()->back()->with('error', implode(' ', $error));
        }

        try {
            $member = Member::find(Crypt::decryptString($member));
            $member->name = $request->name;
            $member->student_id = $request->student_id;
            $member->program_study_id = $request->programStudy;
            $member->status = $request->status;
            $member->is_extraordinary = $request->is_extraordinary;
            $member->start_date = $request->date_start;
            $member->end_date = $request->date_end;
            $member->save();

            return redirect()->back()->with('success', 'Data berhasil diupdate.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('success', 'Coba ulangi beberapa saat lagi.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member): \Illuminate\Http\Response
    {
        $result = Member::find(Crypt::decryptString($member));
        if ($result) {
            $result->delete();

            return redirect()->back()->with('success', 'Data berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }
    }

    public function membersList(Request $request)
    {
        $members = Member::where('name', 'LIKE', '%'.$request->input('q').'%')->orWhere('student_id', 'LIKE', '%'.$request->input('q').'%')
            ->take(10)->get();
        $members = $members->map(function ($member) {
            return [
                'id' => Crypt::encryptString($member->id),
                'name' => $member->name,
                'student_id' => $member->student_id,
            ];
        });

        return response()->json($members);
    }

    public function uid(Request $request)
    {
        $member = Member::where('uid', $request->uid)->where('status', 1)->first();
        $uid_list = Member::wherenotnull('uid')->get()->map(function ($member) {
            return $member->uid;
        });
        if ($member) {
            return response()->json([
                'allowed' => true,
                'allowedUids' => $uid_list,
            ]);
        } else {
            return response()->json([
                'allowed' => false,
                'allowedUids' => $uid_list,
            ]);
        }
    }
}
