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

// New API v1
Route::prefix('v1')->group(function () {
    Route::apiResources([
        'achievement' => AchievementController::class,
        'competition' => CompetitionController::class,
        'competition-organizer-type' => CompetitionOrganizerTypeController::class,
        'competition-output' => CompetitionOutputController::class,
        'competition-rank' => CompetitionRankController::class,
        'competition-scale' => CompetitionScaleController::class,
        'competition-team-type' => CompetitionTeamTypeController::class,
        'competition-time-range' => CompetitionTimeRangeController::class,
        'config' => ConfigController::class,
        'core-team' => CoreTeamController::class,
        'core-team-division' => CoreTeamDivisionController::class,
        'core-team-member' => CoreTeamMemberController::class,
        'degree' => DegreeController::class,
        'faculty' => FacultyController::class,
        'fund-application' => FundApplicationController::class,
        'major' => MajorController::class,
        'post' => PostController::class,
        'project-gallery' => ProjectGalleryController::class,
        'team' => TeamController::class,
        'team-member' => TeamMemberController::class,
        'testimonial' => TestimonialController::class,
        'user' => UserController::class,
    ]);
});
