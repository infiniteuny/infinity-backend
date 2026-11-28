<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionScale\StoreCompetitionScaleRequest;
use App\Http\Requests\CompetitionScale\UpdateCompetitionScaleRequest;
use App\Http\Resources\CompetitionScale\CompetitionScaleCollection;
use App\Http\Resources\CompetitionScale\CompetitionScaleResource;
use App\Models\CompetitionScale;
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Competition Scales
 * Manage competition scales.
 */
class CompetitionScaleController extends Controller
{
    use HasPagination;

    public function __construct()
    {
        $this->authorizeResource(CompetitionScale::class, 'competition_scale');
    }

    /**
     * List all competition scales
     *
     * @apiResourceCollection App\Http\Resources\CompetitionScale\CompetitionScaleCollection
     *
     * @apiResourceModel App\Models\CompetitionScale paginate=10,cursor
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
        $competitionScales = QueryBuilder::for(CompetitionScale::class)
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

        return new CompetitionScaleCollection($competitionScales);
    }

    /**
     * Create a competition scale
     *
     * @apiResource App\Http\Resources\CompetitionScale\CompetitionScaleResource status=201
     *
     * @apiResourceModel App\Models\CompetitionScale
     */
    public function store(StoreCompetitionScaleRequest $request)
    {
        $competitionScale = CompetitionScale::create($request->validated());

        return new CompetitionScaleResource($competitionScale);
    }

    /**
     * Retrieve a competition scale
     *
     * @apiResource App\Http\Resources\CompetitionScale\CompetitionScaleResource
     *
     * @apiResourceModel App\Models\CompetitionScale
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `weight`, `created_at`, `updated_at`. No-example
     */
    public function show(CompetitionScale $competitionScale)
    {
        $competitionScale = QueryBuilder::for(CompetitionScale::where('id', $competitionScale->id))
            ->allowedFields(
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            )
            ->firstOrFail();

        return new CompetitionScaleResource($competitionScale);
    }

    /**
     * Update a competition scale
     *
     * @apiResource App\Http\Resources\CompetitionScale\CompetitionScaleResource
     *
     * @apiResourceModel App\Models\CompetitionScale
     */
    public function update(UpdateCompetitionScaleRequest $request, CompetitionScale $competitionScale)
    {
        $competitionScale->update($request->validated());

        return new CompetitionScaleResource($competitionScale);
    }

    /**
     * Delete a competition scale
     *
     * @apiResource App\Http\Resources\CompetitionScale\CompetitionScaleResource
     *
     * @apiResourceModel App\Models\CompetitionScale
     */
    public function destroy(CompetitionScale $competitionScale)
    {
        $competitionScale->delete();

        return new CompetitionScaleResource($competitionScale);
    }
}
