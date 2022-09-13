<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\LeaderboardController;
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

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/event', [LandingController::class, 'event'])->name('event');
Route::get('/event-detail/{event_id}', [LandingController::class, 'eventDetail'])->name('event.detail');

Route::get('/member', [LandingController::class, 'member'])->name('member');
Route::post('/member', [LandingController::class, 'memberChecker'])->name('member.check');

Route::get('/team', [LandingController::class, 'team'])->name('team');

Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('/leaderboard/{member_id}', [LeaderboardController::class, 'detail'])->name('leaderboard.detail');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';
