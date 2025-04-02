<?php

use App\Http\Controllers\Api\V1\AchievementController;
use App\Http\Controllers\Api\V1\CommunityGroupAdminController;
use App\Http\Controllers\Api\V1\CommunityGroupAdminMemberController;
use App\Http\Controllers\Api\V1\CommunityGroupController;
use App\Http\Controllers\Api\V1\CommunityGroupMemberController;
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
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\GroupPermissionController;
use App\Http\Controllers\Api\V1\MajorController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\PersonaController;
use App\Http\Controllers\Api\V1\ProjectGalleryController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\TeamMemberController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\TokenController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserGroupController;
use App\Http\Controllers\Api\V1\UserPermissionController;
use App\Http\Controllers\Api\V1\UserPersonaController;
use App\Http\Controllers\BlobController;
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

Route::get('static/private/{blob}', BlobController::class)
    ->name('blobs.show');

Route::group([
    'as' => 'api.v1.',
    'prefix' => 'v1',
], function () {
    Route::apiResources([
        'achievements' => AchievementController::class,
        'configs' => ConfigController::class,
        'competitions' => CompetitionController::class,
        'community-groups' => CommunityGroupController::class,
        'community-group-admins' => CommunityGroupAdminController::class,
        'core-teams' => CoreTeamController::class,
        'personas' => PersonaController::class,
        'project-galleries' => ProjectGalleryController::class,
        'testimonials' => TestimonialController::class,
        'users' => UserController::class,
    ], [
        'only' => ['index', 'show'],
    ]);
    Route::apiResources([
        'community-groups.members' => CommunityGroupMemberController::class,
        'community-group-admins.members' => CommunityGroupAdminMemberController::class,
        'core-teams.members' => CoreTeamMemberController::class,
        'users.permissions' => UserPermissionController::class,
        'users.personas' => UserPersonaController::class,
    ], [
        'only' => ['index'],
    ]);
    Route::apiResources([
        'community-group-members' => CommunityGroupMemberController::class,
        'community-group-admin-members' => CommunityGroupAdminMemberController::class,
        'core-team-members' => CoreTeamMemberController::class,
        'user-permissions' => UserPermissionController::class,
        'user-personas' => UserPersonaController::class,
    ], [
        'only' => ['show'],
    ]);

    Route::group([
        'middleware' => ['auth'],
    ], function () {
        Route::apiResource('tokens', TokenController::class)
            ->only(['index', 'show', 'destroy']);
        Route::apiResources([
            'achievements' => AchievementController::class,
            'configs' => ConfigController::class,
            'competitions' => CompetitionController::class,
            'community-groups' => CommunityGroupController::class,
            'community-group-admins' => CommunityGroupAdminController::class,
            'core-teams' => CoreTeamController::class,
            'personas' => PersonaController::class,
            'project-galleries' => ProjectGalleryController::class,
            'testimonials' => TestimonialController::class,
            'users' => UserController::class,
        ], [
            'only' => ['store', 'update', 'destroy'],
        ]);
        Route::apiResources([
            'competition-organizer-types' => CompetitionOrganizerTypeController::class,
            'competition-outputs' => CompetitionOutputController::class,
            'competition-ranks' => CompetitionRankController::class,
            'competition-scales' => CompetitionScaleController::class,
            'competition-team-types' => CompetitionTeamTypeController::class,
            'competition-time-ranges' => CompetitionTimeRangeController::class,
            'core-team-divisions' => CoreTeamDivisionController::class,
            'degrees' => DegreeController::class,
            'faculties' => FacultyController::class,
            'fund-applications' => FundApplicationController::class,
            'groups' => GroupController::class,
            'majors' => MajorController::class,
            'permissions' => PermissionController::class,
            'teams' => TeamController::class,
        ]);
        Route::apiResources([
            'community-groups.members' => CommunityGroupMemberController::class,
            'community-group-admins.members' => CommunityGroupAdminMemberController::class,
            'core-teams.members' => CoreTeamMemberController::class,
            'users.permissions' => UserPermissionController::class,
            'users.personas' => UserPersonaController::class,
        ], [
            'only' => ['store'],
        ]);
        Route::apiResources([
            'groups.permissions' => GroupPermissionController::class,
            'teams.members' => TeamMemberController::class,
            'users.groups' => UserGroupController::class,
        ], [
            'only' => ['index', 'store'],
        ]);
        Route::apiResources([
            'community-group-members' => CommunityGroupMemberController::class,
            'community-group-admin-members' => CommunityGroupAdminMemberController::class,
            'core-team-members' => CoreTeamMemberController::class,
            'user-permissions' => UserPermissionController::class,
            'user-personas' => UserPersonaController::class,
        ], [
            'only' => ['update', 'destroy'],
        ]);
        Route::apiResources([
            'group-permissions' => GroupPermissionController::class,
            'team-members' => TeamMemberController::class,
            'user-groups' => UserGroupController::class,
        ], [
            'only' => ['show', 'update', 'destroy'],
        ]);
    });
});
