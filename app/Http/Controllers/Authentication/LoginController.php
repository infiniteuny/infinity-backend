<?php

namespace App\Http\Controllers\Authentication;

use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

            if (explode("@", $user->email)[1] != "student.uny.ac.id") {
                return redirect()->back()->with('error', 'Email anda tidak terdaftar di Universitas Negeri Yogyakarta');
            }

            $userdata = [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->email,
                'role' => 'student',
                'provider' => 'google',
                'provider_id' => $user->id,
                'avatar' => $user->avatar,
            ];

            if (User::where('email', $user->email)->exists()) {
                $userLogin = User::where('email', $user->email)->first();
                if ($userLogin->avatar != $user->avatar) {
                    $userLogin->avatar = $user->avatar;
                    $userLogin->save();
                }
                Auth::login($userLogin);

                if ($userLogin->getRoleNames()->first() == "admin") {
                    return redirect()->route('admin.dashboard')->with('success', 'Login Berhasil');
                } else {
                    return redirect()->route('student.dashboard')->with('success', 'Login Berhasil');
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
        return redirect()->route('landing');
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
     * @param  \Illuminate\Http\Request  $request
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

            if (Auth::user()->getRoleNames()->first() == "admin") {
                return redirect()->intended('admin')->with('success', 'Login Berhasil');
            } else {
                return redirect()->intended('student')->with('success', 'Login Berhasil');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
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
     * @param  \Illuminate\Http\Request  $request
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
