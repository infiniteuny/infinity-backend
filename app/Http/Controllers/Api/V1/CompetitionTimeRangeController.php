<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionTimeRange\StoreCompetitionTimeRangeRequest;
use App\Http\Requests\CompetitionTimeRange\UpdateCompetitionTimeRangeRequest;
use App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeCollection;
use App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeResource;
use App\Models\CompetitionTimeRange;
use App\Traits\HasPagination;
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
    use HasPagination;

    public function __construct()
    {
        $this->authorizeResource(CompetitionTimeRange::class, 'competition_time_range');
    }

    /**
     * List all competition time ranges
     *
     * @apiResourceCollection App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeCollection
     *
     * @apiResourceModel App\Models\CompetitionTimeRange paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `weight`, `created_at`, `updated_at`. No-example
     * @queryParam filters[name] string Filter by partial `name`. No-example
     * @queryParam filters[weight] integer Filter by exact `weight`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `name`, `weight`, `created_at`, `updated_at`. Default: `weight`, `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $competitionTimeRanges = QueryBuilder::for(CompetitionTimeRange::class)
            ->allowedFields(
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            )
            ->allowedFilters(
                'name',
                AllowedFilter::exact('weight'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            )
            ->defaultSorts(
                'weight',
                '-id',
            )
            ->cursorPaginate($this->resolvePerPage($request));

        return new CompetitionTimeRangeCollection($competitionTimeRanges);
    }

    /**
     * Create a competition time range
     *
     * @apiResource App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeResource status=201
     *
     * @apiResourceModel App\Models\CompetitionTimeRange
     */
    public function store(StoreCompetitionTimeRangeRequest $request)
    {
        $competitionTimeRange = CompetitionTimeRange::create($request->validated());

        return new CompetitionTimeRangeResource($competitionTimeRange);
    }

    /**
     * Retrieve a competition time range
     *
     * @apiResource App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeResource
     *
     * @apiResourceModel App\Models\CompetitionTimeRange
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `weight`, `created_at`, `updated_at`. No-example
     */
    public function show(CompetitionTimeRange $competitionTimeRange)
    {
        $competitionTimeRange = QueryBuilder::for(CompetitionTimeRange::where('id', $competitionTimeRange->id))
            ->allowedFields(
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            )
            ->firstOrFail();

        return new CompetitionTimeRangeResource($competitionTimeRange);
    }

    /**
     * Update a competition time range
     *
     * @apiResource App\Http\Resources\CompetitionTimeRange\CompetitionTimeRangeResource
     *
     * @apiResourceModel App\Models\CompetitionTimeRange
     */
    public function update(UpdateCompetitionTimeRangeRequest $request, CompetitionTimeRange $competitionTimeRange)
    {
        $competitionTimeRange->update($request->validated());

        return new CompetitionTimeRangeResource($competitionTimeRange);
    }

    /**
     * Delete a competition time range
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
