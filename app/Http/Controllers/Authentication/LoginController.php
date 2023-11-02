<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
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

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();

            if (explode('@', $user->email)[1] != 'student.uny.ac.id') {
                return redirect()->back()->with('error', 'Email kamu tidak terdaftar di Universitas Negeri Yogyakarta');
            }

            $userdata = [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->email.$user->id.Str::uuid()->toString(),
                'role' => 'student',
                'provider' => 'google',
                'provider_id' => $user->id,
                'avatar' => $user->avatar,
            ];

            if (User::where('email', $user->email)->exists()) {
                $userLogin = User::where('email', $user->email)->first();
                if ($userLogin->avatar != $user->avatar || $userLogin->name != $user->name) {
                    $userLogin->name = $user->name;
                    $userLogin->avatar = $user->avatar;
                    $userLogin->save();
                }

                if ($userLogin->provider != 'google' || $userLogin->provider_id != $user->id) {
                    $userLogin->provider_id = $user->id;
                    $userLogin->provider = 'google';
                    $userLogin->save();
                }

                if ($userLogin->members->status == 0) {
                    Auth::logout();

                    return redirect()->route('landing')->with('error', 'Yahh, kemarin gaikut daftar ulang ya? Ga bisa login deh!');
                }

                Auth::login($userLogin);

                if ($userLogin->getRoleNames()->first() == 'admin') {
                    return redirect()->route('admin.dashboard')->with('success', 'Login berhasil!');
                } else {
                    return redirect()->route('student.dashboard')->with('success', 'Login berhasil!');
                }
            } else {
                return redirect()->route('register.student-id')->with(['userdata' => $userdata]);
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function redirectToAuthentik()
    {
        return Socialite::driver('authentik')->redirect();
    }

    public function handleAuthentikCallback()
    {
        try {
            $user = Socialite::driver('authentik')->user();

            if (explode('@', $user->email)[1] != 'student.uny.ac.id') {
                return redirect()->back()->with('error', 'Email kamu tidak terdaftar di Universitas Negeri Yogyakarta!');
            }

            $userdata = [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->email.$user->id.Str::uuid()->toString(),
                'role' => 'student',
                'provider' => 'authentik',
                'provider_id' => $user->id,
                'avatar' => $user->avatar,
            ];

            if (User::where('email', $user->email)->exists()) {
                $userLogin = User::where('email', $user->email)->first();
                $userAvatar = $user->avatar ?: 'https://ui-avatars.com/api/?name='.$user->name.'&background=0D8ABC&color=fff';
                if ($userLogin->avatar != $userAvatar || $userLogin->name != $user->name) {
                    $userLogin->name = $user->name;
                    $userLogin->avatar = $userAvatar;
                    $userLogin->save();
                }

                if ($userLogin->provider != 'authentik' || $userLogin->provider_id != $user->id) {
                    $userLogin->provider_id = $user->id;
                    $userLogin->provider = 'authentik';
                    $userLogin->save();
                }

                if ($userLogin->members->status == 0) {
                    Auth::logout();

                    return redirect()->route('landing')->with('error', 'Yahh, kemarin gaikut daftar ulang ya? Ga bisa login deh!');
                }

                Auth::login($userLogin);

                if ($userLogin->getRoleNames()->first() == 'admin') {
                    return redirect()->route('admin.dashboard')->with('success', 'Login berhasil!');
                } else {
                    return redirect()->route('student.dashboard')->with('success', 'Login berhasil!');
                }
            } else {
                return redirect()->route('register.student-id')->with(['userdata' => $userdata]);
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('landing')->with('success', 'Logout berhasil!');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->has('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->members->status == 0) {
                Auth::logout();

                return redirect()->route('landing')->with('error', 'Yahh, kemarin gaikut daftar ulang ya? Ga bisa login deh!');
            }

            if (Auth::user()->getRoleNames()->first() == 'admin') {
                return redirect()->intended('admin')->with('success', 'Login berhasil!');
            } else {
                return redirect()->intended('student')->with('success', 'Login berhasil!');
            }
        }

        return back()->with('error', 'Data yang kamu kirim ga cocok sama database kita nih.')->onlyInput('email');
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
