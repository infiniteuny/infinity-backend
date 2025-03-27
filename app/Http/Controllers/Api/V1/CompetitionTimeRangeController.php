<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionTimeRange\StoreCompetitionTimeRangeRequest;
use App\Http\Requests\CompetitionTimeRange\UpdateCompetitionTimeRangeRequest;
use App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeCollection;
use App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeResource;
use App\Models\CompetitionTimeRange;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Competition Time Ranges
 * Manage competition time ranges.
 */
class CompetitionTimeRangeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CompetitionTimeRange::class, 'competition_time_range');
    }

    /**
     * List all competition time ranges.
     *
     * @apiResourceCollection App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeCollection
     *
     * @apiResourceModel App\Models\CompetitionTimeRange
     */
    public function index(Request $request)
    {
        $competitionTimeRanges = QueryBuilder::for(CompetitionTimeRange::class)
            ->allowedFields([
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            ])
            ->allowedFilters([
                'name',
                AllowedFilter::exact('weight'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            ])
            ->defaultSorts([
                'weight',
                '-id',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new CompetitionTimeRangeCollection($competitionTimeRanges);
    }

    /**
     * Create a competition time range.
     *
     * @apiResource App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeResource
     *
     * @apiResourceModel App\Models\CompetitionTimeRange
     */
    public function store(StoreCompetitionTimeRangeRequest $request)
    {
        $competitionTimeRange = CompetitionTimeRange::create($request->validated());

        return new CompetitionTimeRangeResource($competitionTimeRange);
    }

    /**
     * Retrieve a competition time range.
     *
     * @apiResource App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeResource
     *
     * @apiResourceModel App\Models\CompetitionTimeRange
     */
    public function show(CompetitionTimeRange $competitionTimeRange)
    {
        $competitionTimeRange = QueryBuilder::for(CompetitionTimeRange::where('id', $competitionTimeRange->id))
            ->allowedFields([
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new CompetitionTimeRangeResource($competitionTimeRange);
    }

    /**
     * Update a competition time range.
     */
    public function update(UpdateCompetitionTimeRangeRequest $request, CompetitionTimeRange $competitionTimeRange)
    {
        $competitionTimeRange->update($request->validated());

        return new CompetitionTimeRangeResource($competitionTimeRange);
    }

    /**
     * Delete a competition time range.
     *
     * @apiResource App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeResource
     *
     * @apiResourceModel App\Models\CompetitionTimeRange
     */
    public function destroy(CompetitionTimeRange $competitionTimeRange)
    {
        $competitionTimeRange->delete();

        return new CompetitionTimeRangeResource($competitionTimeRange);
    }
}
