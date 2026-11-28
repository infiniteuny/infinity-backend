<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Major\StoreMajorRequest;
use App\Http\Requests\Major\UpdateMajorRequest;
use App\Http\Resources\Major\MajorCollection;
use App\Http\Resources\Major\MajorResource;
use App\Models\Major;
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Majors
 * Manage majors.
 */
class MajorController extends Controller
{
    use HasPagination;

    public function __construct()
    {
        $this->authorizeResource(Major::class, 'major');
    }

    /**
     * List all majors
     *
     * @apiResourceCollection App\Http\Resources\Major\MajorCollection
     *
     * @apiResourceModel App\Models\Major paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `degree_id`, `faculty_id`, `code`, `name`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `degree`, `faculty`. No-example
     * @queryParam filters[degree_id] string Filter by exact `degree_id`. No-example
     * @queryParam filters[faculty_id] string Filter by exact `faculty_id`. No-example
     * @queryParam filters[code] string Filter by exact `code`. No-example
     * @queryParam filters[name] string Filter by partial `name`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `degree_id`, `faculty_id`, `code`, `name`, `created_at`, `updated_at`. Default: `code`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $majors = QueryBuilder::for(Major::class)
            ->allowedFields(
                'id',
                'degree_id',
                'faculty_id',
                'code',
                'name',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes(
                'degree',
                'faculty',
            )
            ->allowedFilters(
                AllowedFilter::exact('degree_id'),
                AllowedFilter::exact('faculty_id'),
                AllowedFilter::exact('code'),
                'name',
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'degree_id',
                'faculty_id',
                'code',
                'name',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('code');

        $majors = $this->cursorPaginateWithTieBreaker($majors, $request);

        return new MajorCollection($majors);
    }

    /**
     * Create a major
     *
     * @apiResource App\Http\Resources\Major\MajorResource status=201
     *
     * @apiResourceModel App\Models\Major
     */
    public function store(StoreMajorRequest $request)
    {
        $major = Major::create($request->validated());

        return new MajorResource($major);
    }

    /**
     * Retrieve a major
     *
     * @apiResource App\Http\Resources\Major\MajorResource
     *
     * @apiResourceModel App\Models\Major
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `degree_id`, `faculty_id`, `code`, `name`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `degree`, `faculty`. No-example
     */
    public function show(Major $major)
    {
        $major = QueryBuilder::for(Major::where('id', $major->id))
            ->allowedFields(
                'id',
                'degree_id',
                'faculty_id',
                'code',
                'name',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes(
                'degree',
                'faculty',
            )
            ->firstOrFail();

        return new MajorResource($major);
    }

    /**
     * Update a major
     *
     * @apiResource App\Http\Resources\Major\MajorResource
     *
     * @apiResourceModel App\Models\Major
     */
    public function update(UpdateMajorRequest $request, Major $major)
    {
        $major->update($request->validated());

        return new MajorResource($major);
    }

    /**
     * Delete a major
     *
     * @apiResource App\Http\Resources\Major\MajorResource
     *
     * @apiResourceModel App\Models\Major
     */
    public function destroy(Major $major)
    {
        $major->delete();

        return new MajorResource($major);
    }
}
