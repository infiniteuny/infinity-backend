<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\Config;
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
     */
    public function index(): \Illuminate\Http\Response
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
                return redirect()->route('landing')->with('error', 'Email kamu tidak terdaftar di Universitas Negeri Yogyakarta');
            }

            $userdata = [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->email.$user->id.Str::uuid()->toString(),
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

                $config = Config::where('key', 're_registration')->first()->value;
                if ($userLogin->members->status == 0 && $config == 'false') {
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
                return redirect()->route('landing')->with('error', 'Email kamu tidak terdaftar di Universitas Negeri Yogyakarta!');
            }

            $userdata = [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->email.$user->id.Str::uuid()->toString(),
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

                $config = Config::where('key', 're_registration')->first()->value;
                if ($userLogin->members->status == 0 && $config == 'false') {
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
     */
    public function create(): \Illuminate\Http\Response
    {
        return view('auth.login');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\Response
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

            $config = Config::where('key', 're_registration')->first()->value;
            if (Auth::user()->members->status == 0 && $config == 'false') {
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
     */
    public function show(int $id): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): \Illuminate\Http\Response
    {
        //
    }
}
