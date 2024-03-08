<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\Response
    {
        if ($request->ajax()) {
            $users = User::orderBy('name', 'asc')->get();
            $data = $users->map(function ($user) {
                return [
                    'id' => Crypt::encryptString($user->id),
                    'name' => $user->name,
                    'student_id' => $user->student_id,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at ? Carbon::parse($user->email_verified_at)->format('d M Y') : '-',
                    'role' => $user->role,
                    'provider' => $user->provider ? $user->provider : 'website',
                    'avatar' => $user->avatar,
                ];
            });

            return DataTables::of($data)->addIndexColumn()
                ->addIndexColumn()
                ->make(true);
        }

        return view('admin.user.index');
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
    public function store(StoreUserRequest $request): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): \Illuminate\Http\Response
    {
        $user = User::find(Crypt::decryptString($user));
        $roles = Role::all()->map(function ($role) {
            return (object) [
                'id' => Crypt::encryptString($role->id),
                'name' => $role->name,
            ];
        });
        if ($user) {
            $user->roles = $user->getRoleNames()->first();
        }

        return view('admin.user.edit')->with([
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): \Illuminate\Http\Response
    {
        $validate = Validator::make($request->all(), [
            'role' => 'required|exists:roles,name',
            'email' => 'required|string|email|max:255',
            'password' => 'confirmed',
        ]);

        if ($validate->fails()) {
            $error = $validate->errors()->all(':message');

            return redirect()->back()->with('error', implode(' ', $error));
        }

        if (explode('@', $request->email)[1] != 'student.uny.ac.id') {
            return redirect()->back()->with('error', 'Harus menggunakan email student.uny.ac.id');
        }

        try {
            DB::transaction(function () use ($request, $user) {
                $user = User::find(Crypt::decryptString($user));
                $user->removeRole($user->role);

                $user->role = $request->role;
                $user->email = $request->email;
                $user->password = $request->password == '' ? $user->password : bcrypt($request->password);
                $user->save();

                $user->assignRole($request->role);
            });

            return redirect()->back()->with('success', 'Berhasil mengubah data user');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Gagal mengubah data user');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): \Illuminate\Http\Response
    {
        $result = User::find(Crypt::decryptString($user));
        if ($result) {
            $result->delete();

            return redirect()->back()->with('success', 'Data berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }
    }

    public function changePassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validate->fails()) {
            $error = $validate->errors()->all(':message');

            return redirect()->back()->with('error', implode(' ', $error));
        }

        $user = Auth::user();

        if ($request->has('password_old')) {
            if (! Hash::check($request->password_old, $user->password)) {
                return redirect()->back()->with('error', 'Password lama ga sama.');
            }
        }

        try {
            DB::transaction(function () use ($request, $user) {
                $user->password = bcrypt($request->password);
                $user->save();
            });

            return redirect()->back()->with('success', 'Yey! Berhasil ganti password');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Yahh! Gagal ganti password');
        }
    }
}
