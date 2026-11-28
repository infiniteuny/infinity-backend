<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionTeamType\StoreCompetitionTeamTypeRequest;
use App\Http\Requests\CompetitionTeamType\UpdateCompetitionTeamTypeRequest;
use App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeCollection;
use App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeResource;
use App\Models\CompetitionTeamType;
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Competition Team Types
 * Manage competition team types.
 */
class CompetitionTeamTypeController extends Controller
{
    use HasPagination;

    public function __construct()
    {
        $this->authorizeResource(CompetitionTeamType::class, 'competition_team_type');
    }

    /**
     * List all competition team types
     *
     * @apiResourceCollection App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeCollection
     *
     * @apiResourceModel App\Models\CompetitionTeamType paginate=10,cursor
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
        $competitionTeamTypes = QueryBuilder::for(CompetitionTeamType::class)
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

        return new CompetitionTeamTypeCollection($competitionTeamTypes);
    }

    /**
     * Create a competition team type
     *
     * @apiResource App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeResource status=201
     *
     * @apiResourceModel App\Models\CompetitionTeamType
     */
    public function store(StoreCompetitionTeamTypeRequest $request)
    {
        $competitionTeamType = CompetitionTeamType::create($request->validated());

        return new CompetitionTeamTypeResource($competitionTeamType);
    }

    /**
     * Retrieve a competition team type
     *
     * @apiResource App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeResource
     *
     * @apiResourceModel App\Models\CompetitionTeamType
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `weight`, `created_at`, `updated_at`. No-example
     */
    public function show(CompetitionTeamType $competitionTeamType)
    {
        $competitionTeamType = QueryBuilder::for(CompetitionTeamType::where('id', $competitionTeamType->id))
            ->allowedFields(
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            )
            ->firstOrFail();

        return new CompetitionTeamTypeResource($competitionTeamType);
    }

    /**
     * Update a competition team type
     *
     * @apiResource App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeResource
     *
     * @apiResourceModel App\Models\CompetitionTeamType
     */
    public function update(UpdateCompetitionTeamTypeRequest $request, CompetitionTeamType $competitionTeamType)
    {
        $competitionTeamType->update($request->validated());

        return new CompetitionTeamTypeResource($competitionTeamType);
    }

    /**
     * Delete a competition team type
     *
     * @apiResource App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeResource
     *
     * @apiResourceModel App\Models\CompetitionTeamType
     */
    public function destroy(CompetitionTeamType $competitionTeamType)
    {
        $competitionTeamType->delete();

        return new CompetitionTeamTypeResource($competitionTeamType);
    }
}
