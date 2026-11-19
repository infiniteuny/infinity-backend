<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Degree\StoreDegreeRequest;
use App\Http\Requests\Degree\UpdateDegreeRequest;
use App\Http\Resources\Degree\DegreeCollection;
use App\Http\Resources\Degree\DegreeResource;
use App\Models\Degree;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Degrees
 * Manage degrees.
 */
class DegreeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Degree::class, 'degree');
    }

    /**
     * List all degrees
     *
     * @apiResourceCollection App\Http\Resources\Degree\DegreeCollection
     *
     * @apiResourceModel App\Models\Degree paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `code`, `name`, `created_at`, `updated_at`. No-example
     * @queryParam filters[code] string Filter by exact `code`. No-example
     * @queryParam filters[name] string Filter by partial `name`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `code`, `name`, `created_at`, `updated_at`. Default: `code`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $degrees = QueryBuilder::for(Degree::class)
            ->allowedFields(
                'id',
                'code',
                'name',
                'created_at',
                'updated_at',
            )
            ->allowedFilters(
                AllowedFilter::exact('code'),
                'name',
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'code',
                'name',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('code')
            ->cursorPaginate($request->query('per_page', 10));

        return new DegreeCollection($degrees);
    }

    /**
     * Create a degree
     *
     * @apiResource App\Http\Resources\Degree\DegreeResource status=201
     *
     * @apiResourceModel App\Models\Degree
     */
    public function store(StoreDegreeRequest $request)
    {
        $degree = Degree::create($request->validated());

        return new DegreeResource($degree);
    }

    /**
     * Retrieve a degree
     *
     * @apiResource App\Http\Resources\Degree\DegreeResource
     *
     * @apiResourceModel App\Models\Degree
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `code`, `name`, `created_at`, `updated_at`. No-example
     */
    public function show(Degree $degree)
    {
        $degree = QueryBuilder::for(Degree::where('id', $degree->id))
            ->allowedFields(
                'id',
                'code',
                'name',
                'created_at',
                'updated_at',
            )
            ->firstOrFail();

        return new DegreeResource($degree);
    }

    /**
     * Update a degree
     *
     * @apiResource App\Http\Resources\Degree\DegreeResource
     *
     * @apiResourceModel App\Models\Degree
     */
    public function update(UpdateDegreeRequest $request, Degree $degree)
    {
        $degree->update($request->validated());

        return new DegreeResource($degree);
    }

    /**
     * Delete a degree
     *
     * @apiResource App\Http\Resources\Degree\DegreeResource
     *
     * @apiResourceModel App\Models\Degree
     */
    public function destroy(Degree $degree)
    {
        $degree->delete();

        return new DegreeResource($degree);
    }
}
