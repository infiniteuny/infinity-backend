<?php

use App\Http\Controllers\Api\V1\AchievementController;
use App\Http\Controllers\Api\V1\CompetitionController;
use App\Http\Controllers\Api\V1\CompetitionOrganizerTypeController;
use App\Http\Controllers\Api\V1\CompetitionOutputController;
use App\Http\Controllers\Api\V1\CompetitionRankController;
use App\Http\Controllers\Api\V1\CompetitionScaleController;
use App\Http\Controllers\Api\V1\CompetitionTeamTypeController;
use App\Http\Controllers\Api\V1\CompetitionTimeRangeController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\CoreTeamController;
use App\Http\Controllers\Api\V1\CoreTeamDivisionController;
use App\Http\Controllers\Api\V1\CoreTeamMemberController;
use App\Http\Controllers\Api\V1\DegreeController;
use App\Http\Controllers\Api\V1\FundApplicationController;
use App\Http\Controllers\Api\V1\MajorController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProjectGalleryController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\TeamMemberController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\FacultyController;
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
