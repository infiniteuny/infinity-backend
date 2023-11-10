<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function studentId()
    {
        if (Session::get('userdata') == null) {
            return redirect()->route('login');
        }

        return view('auth.check-student-id')->with('userdata', Session::get('userdata'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|max:255|confirmed',
            'student_id' => 'required|integer|unique:users,student_id',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->all(':message');

            return redirect()->route('landing')->with('error', implode(' ', $error))->withInput();
        }

        try {
            if (explode('@', $request->email)[1] != 'student.uny.ac.id') {
                return redirect()->route('landing')->with('error', 'Email kamu tidak terdaftar di Universitas Negeri Yogyakarta!');
            }

            if (Member::where('student_id', $request->student_id)->doesntExist()) {
                return redirect()->route('landing')->with('error', 'NIM kamu tidak terdaftar di database member kami nih.');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => $request->role ?: 'student',
                'provider' => $request->provider ?: null,
                'provider_id' => $request->provider_id ?: null,
                'email_verified_at' => $request->provider ? now() : null,
                'avatar' => $request->avatar ?: 'https://ui-avatars.com/api/?name='.$request->name.'&background=0D8ABC&color=fff',
                'student_id' => $request->student_id,
            ]);

            $user->assignRole('student');

            event(new Registered($user));

            if (Auth::user()->members->status == 0) {
                Auth::logout();

                return redirect()->route('landing')->with('error', 'Yahh, kemarin gaikut daftar ulang ya? Ga bisa login deh!');
            }

            Auth::login($user);

            if ($user->getRoleNames()->first() == 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Akun berhasil dibuat!');
            } else {
                return redirect()->route('student.dashboard')->with('success', 'Akun berhasil dibuat!');
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
