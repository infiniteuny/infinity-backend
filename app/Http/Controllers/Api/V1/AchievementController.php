<?php

namespace App\Http\Controllers\Api\V1;

use App\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Achievement\StoreAchievementRequest;
use App\Http\Requests\Achievement\UpdateAchievementRequest;
use App\Http\Resources\Achievement\AchievementCollection;
use App\Http\Resources\Achievement\AchievementResource;
use App\Jobs\DeleteBlob;
use App\Models\Achievement;
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

/**
 * @group Achievements
 * Manage team's achievements.
 */
class AchievementController extends Controller
{
    use HasPagination;

    public function __construct()
    {
        $this->middleware('can:create,'.Achievement::class)->only('store');
        $this->middleware('can:update,achievement')->only('update');
        $this->middleware('can:delete,achievement')->only('destroy');
    }

    /**
     * List all achievements
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\Achievement\AchievementCollection
     *
     * @apiResourceModel App\Models\Achievement paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `team_id`, `competition_instance_id`, `competition_scale_id`, `competition_time_range_id`, `competition_output_id`, `competition_rank_id`, `competition_branch`, `competition_start_date`, `competition_end_date`, `description`, `image`, `status`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `team`, `team.members`, `competition_instance`, `competition_scale`, `competition_time_range`, `competition_output`, `competition_rank`. (`team` and `team.members` require authentication). No-example
     * @queryParam filters[team_id] string Filter by exact `team_id`. Requires authentication. No-example
     * @queryParam filters[competition_instance_id] string Filter by exact `competition_instance_id`. No-example
     * @queryParam filters[competition_scale_id] string Filter by exact `competition_scale_id`. No-example
     * @queryParam filters[competition_time_range_id] string Filter by exact `competition_time_range_id`. No-example
     * @queryParam filters[competition_output_id] string Filter by exact `competition_output_id`. No-example
     * @queryParam filters[competition_rank_id] string Filter by exact `competition_rank_id`. No-example
     * @queryParam filters[competition_branch] string Filter by partial `competition_branch`. No-example
     * @queryParam filters[competition_start_date] string Filter by `competition_start_date` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[competition_end_date] string Filter by `competition_end_date` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[description] string Filter by partial `description`. No-example
     * @queryParam filters[status] string Filter by exact `status`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `team_id`, `competition_instance_id`, `competition_scale_id`, `competition_time_range_id`, `competition_output_id`, `competition_rank_id`, `competition_branch`, `competition_start_date`, `competition_end_date`, `description`, `status`, `created_at`, `updated_at`. Default: `-id`. (`team_id` requires authentication). No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $user = Auth::guard(config('auth.defaults.semi_public_guard'))->user();

        if ($user) {
            $includes = [
                'team',
                'team.members',
                AllowedInclude::relationship('competition_instance', 'competitionInstance'),
                AllowedInclude::relationship('competition_scale', 'competitionScale'),
                AllowedInclude::relationship('competition_time_range', 'competitionTimeRange'),
                AllowedInclude::relationship('competition_output', 'competitionOutput'),
                AllowedInclude::relationship('competition_rank', 'competitionRank'),
            ];
            $filters = [
                AllowedFilter::exact('team_id'),
                AllowedFilter::exact('competition_instance_id'),
                AllowedFilter::exact('competition_scale_id'),
                AllowedFilter::exact('competition_time_range_id'),
                AllowedFilter::exact('competition_output_id'),
                AllowedFilter::exact('competition_rank_id'),
                'competition_branch',
                AllowedFilter::operator('competition_start_date', FilterOperator::DYNAMIC),
                AllowedFilter::operator('competition_end_date', FilterOperator::DYNAMIC),
                'description',
                AllowedFilter::exact('status'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ];
            $sorts = [
                'id',
                'team_id',
                'competition_instance_id',
                'competition_scale_id',
                'competition_time_range_id',
                'competition_output_id',
                'competition_rank_id',
                'competition_branch',
                'competition_start_date',
                'competition_end_date',
                'description',
                'status',
                'created_at',
                'updated_at',
            ];
        } else {
            $includes = [
                AllowedInclude::relationship('competition_instance', 'competitionInstance'),
                AllowedInclude::relationship('competition_scale', 'competitionScale'),
                AllowedInclude::relationship('competition_time_range', 'competitionTimeRange'),
                AllowedInclude::relationship('competition_output', 'competitionOutput'),
                AllowedInclude::relationship('competition_rank', 'competitionRank'),
            ];
            $filters = [
                AllowedFilter::exact('competition_instance_id'),
                AllowedFilter::exact('competition_scale_id'),
                AllowedFilter::exact('competition_time_range_id'),
                AllowedFilter::exact('competition_output_id'),
                AllowedFilter::exact('competition_rank_id'),
                'competition_branch',
                AllowedFilter::operator('competition_start_date', FilterOperator::DYNAMIC),
                AllowedFilter::operator('competition_end_date', FilterOperator::DYNAMIC),
                'description',
                AllowedFilter::exact('status'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ];
            $sorts = [
                'id',
                'competition_instance_id',
                'competition_scale_id',
                'competition_time_range_id',
                'competition_output_id',
                'competition_rank_id',
                'competition_branch',
                'competition_start_date',
                'competition_end_date',
                'description',
                'status',
                'created_at',
                'updated_at',
            ];
        }

        $achievements = QueryBuilder::for(Achievement::class)
            ->allowedFields(
                'id',
                'team_id',
                'competition_instance_id',
                'competition_scale_id',
                'competition_time_range_id',
                'competition_output_id',
                'competition_rank_id',
                'competition_branch',
                'competition_start_date',
                'competition_end_date',
                'description',
                'image',
                'status',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes(...$includes)
            ->allowedFilters(...$filters)
            ->allowedSorts(...$sorts)
            ->defaultSorts('-id');

        $achievements = $achievements->cursorPaginate($this->resolvePerPage($request));

        return new AchievementCollection($achievements);
    }

    /**
     * Create an achievement
     *
     * @apiResource App\Http\Resources\Achievement\AchievementResource status=201
     *
     * @apiResourceModel App\Models\Achievement
     */
    public function store(StoreAchievementRequest $request)
    {
        $manifest = Storage::store($request->file('image'), 'achievements/images');

        try {
            $achievement = Achievement::create(
                array_replace($request->validated(), ['image' => $manifest])
            );
        } catch (Throwable $e) {
            if (is_string($manifest) && $manifest !== '') {
                DeleteBlob::dispatch($manifest);
            }

            throw $e;
        }

        return new AchievementResource($achievement);
    }

    /**
     * Retrieve an achievement
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\Achievement\AchievementResource
     *
     * @apiResourceModel App\Models\Achievement
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `team_id`, `competition_instance_id`, `competition_scale_id`, `competition_time_range_id`, `competition_output_id`, `competition_rank_id`, `competition_branch`, `competition_start_date`, `competition_end_date`, `description`, `image`, `status`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `team`, `team.members`, `competition_instance`, `competition_scale`, `competition_time_range`, `competition_output`, `competition_rank`. (`team` and `team.members` require authentication). No-example
     */
    public function show(Achievement $achievement)
    {
        $user = Auth::guard(config('auth.defaults.semi_public_guard'))->user();

        if ($user) {
            $includes = [
                'team',
                'team.members',
                AllowedInclude::relationship('competition_instance', 'competitionInstance'),
                AllowedInclude::relationship('competition_scale', 'competitionScale'),
                AllowedInclude::relationship('competition_time_range', 'competitionTimeRange'),
                AllowedInclude::relationship('competition_output', 'competitionOutput'),
                AllowedInclude::relationship('competition_rank', 'competitionRank'),
            ];
        } else {
            $includes = [
                AllowedInclude::relationship('competition_instance', 'competitionInstance'),
                AllowedInclude::relationship('competition_scale', 'competitionScale'),
                AllowedInclude::relationship('competition_time_range', 'competitionTimeRange'),
                AllowedInclude::relationship('competition_output', 'competitionOutput'),
                AllowedInclude::relationship('competition_rank', 'competitionRank'),
            ];
        }

        $achievement = QueryBuilder::for(Achievement::where('id', $achievement->id))
            ->allowedFields(
                'id',
                'team_id',
                'competition_instance_id',
                'competition_scale_id',
                'competition_time_range_id',
                'competition_output_id',
                'competition_rank_id',
                'competition_branch',
                'competition_start_date',
                'competition_end_date',
                'description',
                'image',
                'status',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes(...$includes)
            ->firstOrFail();

        return new AchievementResource($achievement);
    }

    /**
     * Update an achievement
     *
     * @apiResource App\Http\Resources\Achievement\AchievementResource
     *
     * @apiResourceModel App\Models\Achievement
     */
    public function update(UpdateAchievementRequest $request, Achievement $achievement)
    {
        $hasImage = $request->has('image');
        $oldManifest = null;
        $manifest = null;

        if ($hasImage) {
            $oldManifest = $achievement->getRawOriginal('image');
            $manifest = Storage::store($request->file('image'), 'achievements/images');
        }

        try {
            $achievement->update(
                $hasImage
                    ? array_replace($request->validated(), ['image' => $manifest])
                    : $request->validated()
            );
        } catch (Throwable $e) {
            if (is_string($manifest) && $manifest !== '') {
                DeleteBlob::dispatch($manifest);
            }

            throw $e;
        }

        if (is_string($oldManifest) && $oldManifest !== '') {
            DeleteBlob::dispatch($oldManifest);
        }

        return new AchievementResource($achievement);
    }

    /**
     * Delete an achievement
     *
     * @apiResource App\Http\Resources\Achievement\AchievementResource
     *
     * @apiResourceModel App\Models\Achievement
     */
    public function destroy(Achievement $achievement)
    {
        $manifest = $achievement->getRawOriginal('image');

        $achievement->delete();

        if (is_string($manifest) && $manifest !== '') {
            DeleteBlob::dispatch($manifest);
        }

        return new AchievementResource($achievement);
    }
}
