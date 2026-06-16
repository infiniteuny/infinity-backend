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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Achievements
 * Manage team's achievements.
 */
class AchievementController extends Controller
{
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
     */
    public function index(Request $request)
    {
        $user = Auth::guard(config('auth.defaults.semi_public_guard'))->user();
        $userId = $user->id;

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
                AllowedFilter::exact('competition_team_type_id'),
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
                'competition_team_type_id',
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
                AllowedFilter::exact('competition_team_type_id'),
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
                'competition_team_type_id',
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
                'name',
                'team_id',
                'competition_instance_id',
                'competition_team_type_id',
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

        $achievements = $achievements->cursorPaginate($request->query('per_page', 10));

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

        $achievement = Achievement::create(
            array_replace($request->validated(), ['image' => $manifest])
        );

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
     */
    public function show(Achievement $achievement)
    {
        $user = Auth::guard(config('auth.defaults.semi_public_guard'))->user();
        $userId = $user->id;

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
                'name',
                'team_id',
                'competition_instance_id',
                'competition_team_type_id',
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

        if ($hasImage) {
            $oldManifest = $achievement->getRawOriginal('image');

            DeleteBlob::dispatch($oldManifest);

            $manifest = Storage::store($request->file('image'), 'achievements/images');
        }

        $achievement->update(
            $hasImage
                ? array_replace($request->validated(), ['image' => $manifest])
                : $request->validated()
        );

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

        DeleteBlob::dispatch($manifest);

        $achievement->delete();

        return new AchievementResource($achievement);
    }
}
