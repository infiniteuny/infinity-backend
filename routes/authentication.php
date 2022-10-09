<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authentication\LoginController;
use App\Http\Controllers\Authentication\RegisterController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::middleware('guest')->group(
    function () {
        Route::get('register', [RegisterController::class, 'create'])->name('register');
        Route::get('register/student-id', [RegisterController::class, 'studentId'])->name('register.student-id');
        Route::post('register', [RegisterController::class, 'store']);
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store']);
        Route::get('login/google', [LoginController::class, 'redirectToGoogle'])->name('login.google');
        Route::get('login/google/callback', [LoginController::class, 'handleGoogleCallback']);
    }
);

Route::middleware('auth')->group(
    function () {
        Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

        Route::get('/email/verify', function () {
            return view('auth.verify-email');
        })->middleware('auth')->name('verification.notice');

        Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
            $request->fulfill();
            switch (Auth::user()->getRoleNames()[0]) {
                case 'admin':
                    return redirect('/admin')->with('success', 'Login Berhasil');
                    break;
                case 'student':
                    return redirect('/student')->with('success', 'Login Berhasil');
                    break;
                default:
                    return redirect('/');
                    break;
            }
        })->middleware(['auth', 'signed'])->name('verification.verify');

        Route::post('/email/verification-notification', function (Request $request) {
            $request->user()->sendEmailVerificationNotification();

            return back()->with('success', 'Verification link sent!');
        })->middleware(['auth', 'throttle:6,1'])->name('verification.send');
    }
);
