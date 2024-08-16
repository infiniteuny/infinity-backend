<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Achievement\StoreAchievementRequest;
use App\Http\Requests\Achievement\UpdateAchievementRequest;
use App\Jobs\DeleteBlob;
use App\Models\Achievement;
use App\Repositories\StorageFacade;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class AchievementController extends Controller
{
    public function __construct(
        protected StorageFacade $storageFacade,
    ) {
        // $this->authorizeResource(Achievement::class, 'achievement');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $achievements = QueryBuilder::for(Achievement::class)
            ->allowedFilters([
                'team_id',
                'competition_id',
                'competition_team_type_id',
                'competition_scale_id',
                'competition_time_range_id',
                'competition_output_id',
                'competition_rank_id',
                'competition_branch',
                'competition_date',
                'description',
                'image',
                'status',
            ])
            ->defaultSorts([
                '-created_at',
                'id',
            ])
            ->allowedSorts([
                'id',
                'team_id',
                'competition_id',
                'competition_team_type_id',
                'competition_scale_id',
                'competition_time_range_id',
                'competition_output_id',
                'competition_rank_id',
                'competition_branch',
                'competition_date',
                'description',
                'image',
                'status',
                'created_at',
                'updated_at',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('achievements', $achievements);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAchievementRequest $request): JsonResponse
    {
        $manifest = $this->storageFacade->store($request->file('image'), 'images/achievements');

        $achievement = Achievement::create(
            array_replace($request->validated(), ['image' => $manifest])
        );

        return ResponseFormatter::singleton('achievement', $achievement, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Achievement $achievement): JsonResponse
    {
        return ResponseFormatter::singleton('achievement', $achievement);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAchievementRequest $request, Achievement $achievement): JsonResponse
    {
        $hasImage = $request->has('image');

        if ($hasImage) {
            $encodedManifest = $achievement->getRawOriginal('image');

            dispatch(new DeleteBlob($encodedManifest));

            $manifest = $this->storageFacade->store($request->file('image'), 'images/achievements');
        }

        $achievement->update(
            $hasImage
                ? array_replace($request->validated(), ['image' => $manifest])
                : $request->validated()
        );

        return ResponseFormatter::singleton('achievement', $achievement);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Achievement $achievement): JsonResponse
    {
        $encodedManifest = $achievement->getRawOriginal('image');

        dispatch(new DeleteBlob($encodedManifest));

        $achievement->delete();

        return ResponseFormatter::singleton('achievement', $achievement);
    }
}
