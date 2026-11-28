<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionOrganizerType\StoreCompetitionOrganizerTypeRequest;
use App\Http\Requests\CompetitionOrganizerType\UpdateCompetitionOrganizerTypeRequest;
use App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeCollection;
use App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeResource;
use App\Models\CompetitionOrganizerType;
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Competition Organizer Types
 * Manage competition organizer types.
 */
class CompetitionOrganizerTypeController extends Controller
{
    use HasPagination;

    public function __construct()
    {
        $this->authorizeResource(CompetitionOrganizerType::class, 'competition_organizer_type');
    }

    /**
     * List all competition organizer types
     *
     * @apiResourceCollection App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeCollection
     *
     * @apiResourceModel App\Models\CompetitionOrganizerType paginate=10,cursor
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
        $competitionOrganizerTypes = QueryBuilder::for(CompetitionOrganizerType::class)
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
            );

        $competitionOrganizerTypes = $this->cursorPaginateWithTieBreaker($competitionOrganizerTypes, $request);

        return new CompetitionOrganizerTypeCollection($competitionOrganizerTypes);
    }

    /**
     * Create a competition organizer type
     *
     * @apiResource App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeResource status=201
     *
     * @apiResourceModel App\Models\CompetitionOrganizerType
     */
    public function store(StoreCompetitionOrganizerTypeRequest $request)
    {
        $competitionOrganizerType = CompetitionOrganizerType::create($request->validated());

        return new CompetitionOrganizerTypeResource($competitionOrganizerType);
    }

    /**
     * Retrieve a competition organizer type
     *
     * @apiResource App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeResource
     *
     * @apiResourceModel App\Models\CompetitionOrganizerType
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `weight`, `created_at`, `updated_at`. No-example
     */
    public function show(CompetitionOrganizerType $competitionOrganizerType)
    {
        $competitionOrganizerType = QueryBuilder::for(CompetitionOrganizerType::where('id', $competitionOrganizerType->id))
            ->allowedFields(
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            )
            ->firstOrFail();

        return new CompetitionOrganizerTypeResource($competitionOrganizerType);
    }

    /**
     * Update a competition organizer type
     *
     * @apiResource App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeResource
     *
     * @apiResourceModel App\Models\CompetitionOrganizerType
     */
    public function update(UpdateCompetitionOrganizerTypeRequest $request, CompetitionOrganizerType $competitionOrganizerType)
    {
        $competitionOrganizerType->update($request->validated());

        return new CompetitionOrganizerTypeResource($competitionOrganizerType);
    }

    /**
     * Delete a competition organizer type
     *
     * @apiResource App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeResource
     *
     * @apiResourceModel App\Models\CompetitionOrganizerType
     */
    public function destroy(CompetitionOrganizerType $competitionOrganizerType)
    {
        $competitionOrganizerType->delete();

        return new CompetitionOrganizerTypeResource($competitionOrganizerType);
    }
}
