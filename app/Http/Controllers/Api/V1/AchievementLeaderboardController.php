<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AchievementLeaderboard\AchievementLeaderboardCollection;
use App\Http\Resources\AchievementLeaderboard\AchievementLeaderboardYearCollection;
use App\Models\AchievementLeaderboard;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Leaderboards
 * Retrieve leaderboards.
 */
class AchievementLeaderboardController extends Controller
{
    /**
     * List all achievement leaderboard years
     *
     * @unauthenticated
     */
    public function index(Request $request)
    {
        $years = QueryBuilder::for(AchievementLeaderboard::query()
            ->selectRaw('DISTINCT year'))
            ->allowedSorts([
                'year',
            ])
            ->defaultSorts([
                '-year',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new AchievementLeaderboardYearCollection($years);
    }

    /**
     * Retrieve an achievement leaderboard
     *
     * @unauthenticated
     */
    public function show(Request $request, string $achievement)
    {
        $achievementLeaderboards = QueryBuilder::for(AchievementLeaderboard::where('year', $achievement))
            ->allowedFields([
                'id',
                'user_id',
                'year',
                'total_points',
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes([
                'user',
            ])
            ->allowedFilters([
                'user_id',
                AllowedFilter::operator('total_points', FilterOperator::DYNAMIC),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'user_id',
                'total_points',
                'created_at',
                'updated_at',
            ])
            ->defaultSorts([
                '-total_points',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new AchievementLeaderboardCollection($achievementLeaderboards);
    }
}
