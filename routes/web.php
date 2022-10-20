<?php

use App\Http\Controllers\AchievementController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReregistrationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

require __DIR__ . '/authentication.php';

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/event', [LandingController::class, 'event'])->name('event');
Route::get('/event-detail/{event_id}', [LandingController::class, 'eventDetail'])->name('event.detail');

Route::get('/member', [LandingController::class, 'member'])->name('member');
Route::post('/member', [LandingController::class, 'memberChecker'])->name('member.check');

Route::get('/team', [LandingController::class, 'team'])->name('team');

Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('/leaderboard/{member_id}', [LeaderboardController::class, 'detail'])->name('leaderboard.detail');

Route::post('/contact-us', [LandingController::class, 'contactUs'])->name('contact-us');

Route::prefix('admin')->name('admin.')->middleware(['role:admin', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'adminDashboard'])->name('dashboard');
    Route::prefix('config')->name('config.')->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::put('/', [ConfigController::class, 'update'])->name('update');
    });
    Route::resources([
        'member' => MemberController::class,
        'achievement' => AchievementController::class,
        'user' => UserController::class,
    ]);
});

Route::prefix('student')->name('student.')->middleware(['role:student', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'studentDashboard'])->name('dashboard');
    Route::get('coming-soon', function () {
        return redirect()->back()->with('error', 'Coming Soon!');
    })->name('coming-soon');
    Route::resource('re-registration', ReregistrationController::class)->only(['index', 'store']);
    // Route::get('/', function () {
    //     return view('errors.503');
    // })->name('dashboard');
});
