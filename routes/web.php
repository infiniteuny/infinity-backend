<?php

use App\Http\Controllers\AchievementController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FreepikDownloadController;
use App\Http\Controllers\FundApplicationController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReregistrationController;
use App\Http\Controllers\SaweriaWebhookController;
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

Route::post('webhook', [SaweriaWebhookController::class, 'webhook'])->name('webhook');

Route::post('change-password', [UserController::class, 'changePassword'])->name('change-password')->middleware('auth', 'verified');

Route::get('coming-soon', function () {
    return redirect()->back()->with('error', 'Coming Soon!');
})->name('coming-soon');

// ---------------- Admin Route ----------------

Route::prefix('admin')->name('admin.')->middleware(['role:admin', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'adminDashboard'])->name('dashboard');
    Route::prefix('config')->name('config.')->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->name('index');
        Route::put('/', [ConfigController::class, 'update'])->name('update');
    });
    Route::prefix('achievement')->name('achievement.')->group(function () {
        Route::get('/{achievement}/accept', [AchievementController::class, 'accept'])->name('accept');
        Route::get('/{achievement}/reject', [AchievementController::class, 'reject'])->name('reject');
    });
    Route::prefix('fund-application')->name('fund-application.')->group(function () {
        Route::prefix('{fund_application}/download')->name('download.')->group(function () {
            Route::post('student-id-card', [FundApplicationController::class, 'downloadStudentIdCard'])->name('student-id-card');
            Route::post('letter-of-acceptance', [FundApplicationController::class, 'downloadLetterOfAcceptance'])->name('letter-of-acceptance');
            Route::post('budget-plan', [FundApplicationController::class, 'downloadBudgetPlan'])->name('budget-plan');
        });
        Route::get('/{fund_application}/accept', [FundApplicationController::class, 'accept'])->name('accept');
        Route::get('/{fund_application}/reject', [FundApplicationController::class, 'reject'])->name('reject');
    });
    Route::post('freepik/{freepik}/download', [FreepikDownloadController::class, 'download'])->name('freepik.download');
    Route::resource('freepik', FreepikDownloadController::class)->except('create', 'show', 'edit', 'update', 'destroy');
    Route::resources([
        'member' => MemberController::class,
        'achievement' => AchievementController::class,
        'user' => UserController::class,
        'fund-application' => FundApplicationController::class,
    ]);
});


// ---------------- Student Route ----------------

Route::prefix('student')->name('student.')->middleware(['role:student', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'studentDashboard'])->name('dashboard');
    Route::resource('re-registration', ReregistrationController::class)->only(['index', 'store']);

    Route::get('achievement', [AchievementController::class, 'studentIndex'])->name('achievement.index');
    Route::get('achievement/{achievement}/edit', [AchievementController::class, 'studentEdit'])->name('achievement.edit');
    Route::resource('achievement', AchievementController::class)->only('store', 'update', 'destroy');

    Route::prefix('fund-application/{fund_application}/download')->name('fund-application.download.')->group(function () {
        Route::post('student-id-card', [FundApplicationController::class, 'downloadStudentIdCard'])->name('student-id-card');
        Route::post('letter-of-acceptance', [FundApplicationController::class, 'downloadLetterOfAcceptance'])->name('letter-of-acceptance');
        Route::post('budget-plan', [FundApplicationController::class, 'downloadBudgetPlan'])->name('budget-plan');
    });
    Route::resource('fund-application', FundApplicationController::class);
    Route::resource('freepik', FreepikDownloadController::class)->except('create', 'show', 'edit', 'update', 'destroy');
    Route::post('freepik/{freepik}/download', [FreepikDownloadController::class, 'download'])->name('freepik.download');
});
