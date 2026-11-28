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
     *
     * @apiResourceCollection App\Http\Resources\AchievementLeaderboard\AchievementLeaderboardYearCollection
     *
     * @apiResourceModel App\Models\AchievementLeaderboard paginate=10,cursor
     *
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `year`. Default: `-year`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $years = QueryBuilder::for(AchievementLeaderboard::query()
            ->selectRaw('DISTINCT year'))
            ->allowedSorts('year')
            ->defaultSorts('-year')
            ->cursorPaginate($request->query('per_page', 10));

        return new AchievementLeaderboardYearCollection($years);
    }

    /**
     * Retrieve an achievement leaderboard
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\AchievementLeaderboard\AchievementLeaderboardCollection
     *
     * @apiResourceModel App\Models\AchievementLeaderboard paginate=10,cursor
     *
     * @urlParam achievement integer required The year of the leaderboard. Example: 2026
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `user_id`, `year`, `total_points`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `user`. No-example
     * @queryParam filters[user_id] string Filter by exact `user_id`. No-example
     * @queryParam filters[total_points] string Filter by `total_points` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `user_id`, `total_points`, `created_at`, `updated_at`. Default: `-total_points,id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function show(Request $request, string $achievement)
    {
        abort_unless(ctype_digit($achievement), 404);

        $year = filter_var($achievement, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 2147483647],
        ]);
        $subject = $year !== false
            ? AchievementLeaderboard::where('year', $year)
            : AchievementLeaderboard::whereRaw('1 = 0');

        $query = QueryBuilder::for($subject)
            ->allowedFields(
                'id',
                'user_id',
                'year',
                'total_points',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes('user')
            ->allowedFilters(
                AllowedFilter::exact('user_id'),
                AllowedFilter::operator('total_points', FilterOperator::DYNAMIC),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'user_id',
                'total_points',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('-total_points', 'id');

        $baseQuery = $query->getEloquentBuilder()->getQuery();
        $hasUniqueSort = collect($baseQuery->orders ?? [])->contains(
            fn (array $order) => in_array(
                $order['column'] ?? null,
                ['id', 'achievement_leaderboards.id', 'user_id', 'achievement_leaderboards.user_id'],
                true,
            ),
        );

        if (! $hasUniqueSort) {
            $columns = $baseQuery->columns;
            if (
                is_array($columns)
                && ! in_array('*', $columns, true)
                && ! in_array('id', $columns, true)
                && ! in_array('achievement_leaderboards.id', $columns, true)
            ) {
                $query->getEloquentBuilder()->addSelect('id');
            }

            $query->orderBy('id');
        }

        $achievementLeaderboards = $query->cursorPaginate($request->query('per_page', 10));

        return new AchievementLeaderboardCollection($achievementLeaderboards);
    }
}
