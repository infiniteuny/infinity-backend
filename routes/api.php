<?php

use App\Http\Controllers\Api\V1\AchievementController;
use App\Http\Controllers\Api\V1\BlobController;
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
use App\Http\Controllers\Api\V1\FacultyController;
use App\Http\Controllers\Api\V1\FundApplicationController;
use App\Http\Controllers\Api\V1\MajorController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProjectGalleryController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\TeamMemberController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\UserController;
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

Route::group([
    'as' => 'api.v1.',
    'prefix' => 'v1',
], function () {
    Route::get('blobs/{blob}', BlobController::class)
        ->name('blobs.show');

    Route::apiResources([
        'achievements' => AchievementController::class,
        'competitions' => CompetitionController::class,
        'competition-organizer-types' => CompetitionOrganizerTypeController::class,
        'competition-outputs' => CompetitionOutputController::class,
        'competition-ranks' => CompetitionRankController::class,
        'competition-scales' => CompetitionScaleController::class,
        'competition-team-types' => CompetitionTeamTypeController::class,
        'competition-time-ranges' => CompetitionTimeRangeController::class,
        'configs' => ConfigController::class,
        'core-teams' => CoreTeamController::class,
        'core-team-divisions' => CoreTeamDivisionController::class,
        'core-team-members' => CoreTeamMemberController::class,
        'degrees' => DegreeController::class,
        'faculties' => FacultyController::class,
        'fund-applications' => FundApplicationController::class,
        'majors' => MajorController::class,
        'posts' => PostController::class,
        'project-galleries' => ProjectGalleryController::class,
        'teams' => TeamController::class,
        'team-members' => TeamMemberController::class,
        'testimonials' => TestimonialController::class,
        'users' => UserController::class,
    ]);
});
