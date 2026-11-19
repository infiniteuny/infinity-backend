<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoreTeamDivision\StoreCoreTeamDivisionRequest;
use App\Http\Requests\CoreTeamDivision\UpdateCoreTeamDivisionRequest;
use App\Http\Resources\CoreTeamDivision\CoreTeamDivisionCollection;
use App\Http\Resources\CoreTeamDivision\CoreTeamDivisionResource;
use App\Models\CoreTeamDivision;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Core Team Divisions
 * Manage core team divisions.
 */
class CoreTeamDivisionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CoreTeamDivision::class, 'core_team_division');
    }

    /**
     * List all core team divisions
     *
     * @apiResourceCollection App\Http\Resources\CoreTeamDivision\CoreTeamDivisionCollection
     *
     * @apiResourceModel App\Models\CoreTeamDivision paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `priority`, `created_at`, `updated_at`. No-example
     * @queryParam filters[name] string Filter by partial `name`. No-example
     * @queryParam filters[priority] integer Filter by exact `priority`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `name`, `priority`, `created_at`, `updated_at`. Default: `priority`, `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $coreTeamDivisions = QueryBuilder::for(CoreTeamDivision::class)
            ->allowedFields(
                'id',
                'name',
                'priority',
                'created_at',
                'updated_at',
            )
            ->allowedFilters(
                'name',
                AllowedFilter::exact('priority'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'name',
                'priority',
                'created_at',
                'updated_at',
            )
            ->defaultSorts(
                'priority',
                '-id',
            )
            ->cursorPaginate($request->query('per_page', 10));

        return new CoreTeamDivisionCollection($coreTeamDivisions);
    }

    /**
     * Create a core team division
     *
     * @apiResource App\Http\Resources\CoreTeamDivision\CoreTeamDivisionResource status=201
     *
     * @apiResourceModel App\Models\CoreTeamDivision
     */
    public function store(StoreCoreTeamDivisionRequest $request)
    {
        $coreTeamDivision = CoreTeamDivision::create($request->validated());

        return new CoreTeamDivisionResource($coreTeamDivision);
    }

    /**
     * Retrieve a core team division
     *
     * @apiResource App\Http\Resources\CoreTeamDivision\CoreTeamDivisionResource
     *
     * @apiResourceModel App\Models\CoreTeamDivision
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `priority`, `created_at`, `updated_at`. No-example
     */
    public function show(CoreTeamDivision $coreTeamDivision)
    {
        $coreTeamDivision = QueryBuilder::for(CoreTeamDivision::where('id', $coreTeamDivision->id))
            ->allowedFields(
                'id',
                'name',
                'priority',
                'created_at',
                'updated_at',
            )
            ->firstOrFail();

        return new CoreTeamDivisionResource($coreTeamDivision);
    }

    /**
     * Update a core team division
     *
     * @apiResource App\Http\Resources\CoreTeamDivision\CoreTeamDivisionResource
     *
     * @apiResourceModel App\Models\CoreTeamDivision
     */
    public function update(UpdateCoreTeamDivisionRequest $request, CoreTeamDivision $coreTeamDivision)
    {
        $coreTeamDivision->update($request->validated());

        return new CoreTeamDivisionResource($coreTeamDivision);
    }

    /**
     * Delete a core team division
     *
     * @apiResource App\Http\Resources\CoreTeamDivision\CoreTeamDivisionResource
     *
     * @apiResourceModel App\Models\CoreTeamDivision
     */
    public function destroy(CoreTeamDivision $coreTeamDivision)
    {
        $coreTeamDivision->delete();

        return new CoreTeamDivisionResource($coreTeamDivision);
    }
}
