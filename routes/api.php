<?php

use App\Http\Controllers\Api\v1\AchievementController;
use App\Http\Controllers\Api\v1\CompetitionController;
use App\Http\Controllers\Api\v1\CompetitionOrganizerTypeController;
use App\Http\Controllers\Api\v1\CompetitionOutputController;
use App\Http\Controllers\Api\v1\CompetitionRankController;
use App\Http\Controllers\Api\v1\CompetitionScaleController;
use App\Http\Controllers\Api\v1\CompetitionTeamTypeController;
use App\Http\Controllers\Api\v1\CompetitionTimeRangeController;
use App\Http\Controllers\Api\v1\ConfigController;
use App\Http\Controllers\Api\v1\CoreTeamController;
use App\Http\Controllers\Api\v1\CoreTeamDivisionController;
use App\Http\Controllers\Api\v1\CoreTeamMemberController;
use App\Http\Controllers\Api\v1\DegreeController;
use App\Http\Controllers\Api\v1\FundApplicationController;
use App\Http\Controllers\Api\v1\MajorController;
use App\Http\Controllers\Api\v1\PostController;
use App\Http\Controllers\Api\v1\ProjectGalleryController;
use App\Http\Controllers\Api\v1\TeamController;
use App\Http\Controllers\Api\v1\TeamMemberController;
use App\Http\Controllers\Api\v1\TestimonialController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\MemberController;
use App\Models\CompetitionOrganizerType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/faculties', [FacultyController::class, 'facultiesList'])->name('faculties.list');
Route::get('/faculties/{faculty}/program-studies', [FacultyController::class, 'programStudiesList'])->name('faculties.program-studies.list');
Route::get('/members', [MemberController::class, 'membersList'])->name('members.list');

Route::middleware('auth:sanctum')->post('/uid', [MemberController::class, 'uid'])->name('members.uid');

// NEW DASHBOARD
Route::resource('achievement', AchievementController::class);
Route::resource('competition', CompetitionController::class);
Route::resource('competition-organizer-type', CompetitionOrganizerTypeController::class);
Route::resource('competition-output', CompetitionOutputController::class);
Route::resource('competition-rank', CompetitionRankController::class);
Route::resource('competition-scale', CompetitionScaleController::class);
Route::resource('competition-team-type', CompetitionTeamTypeController::class);
Route::resource('competition-time-range', CompetitionTimeRangeController::class);
Route::resource('config', ConfigController::class);
Route::resource('core-team', CoreTeamController::class);
Route::resource('core-team-division', CoreTeamDivisionController::class);
Route::resource('core-team-member', CoreTeamMemberController::class);
Route::resource('degree', DegreeController::class);
Route::resource('faculty', FacultyController::class);
Route::resource('fund-application', FundApplicationController::class);
Route::resource('major', MajorController::class);
Route::resource('post', PostController::class);
Route::resource('project-gallery', ProjectGalleryController::class);
Route::resource('team', TeamController::class);
Route::resource('team-member', TeamMemberController::class);
Route::resource('testimonial', TestimonialController::class);
Route::resource('user', UserController::class);
