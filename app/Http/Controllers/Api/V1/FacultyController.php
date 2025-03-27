<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faculty\StoreFacultyRequest;
use App\Http\Requests\Faculty\UpdateFacultyRequest;
use App\Http\Resources\Faculty\FacultyCollection;
use App\Http\Resources\Faculty\FacultyResource;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Faculties
 * Manage faculties.
 */
class FacultyController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Faculty::class, 'faculty');
    }

    /**
     * List all faculties.
     *
     * @apiResourceCollection App\Http\Resources\Faculty\FacultyCollection
     *
     * @apiResourceModel App\Models\Faculty
     */
    public function index(Request $request)
    {
        $faculties = QueryBuilder::for(Faculty::class)
            ->allowedFields([
                'id',
                'code',
                'name',
                'created_at',
                'updated_at',
            ])
            ->allowedFilters([
                AllowedFilter::exact('code'),
                'name',
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'code',
                'name',
                'created_at',
                'updated_at',
            ])
            ->defaultSorts([
                'code',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new FacultyCollection($faculties);
    }

    /**
     * Create a faculty.
     *
     * @apiResource App\Http\Resources\Faculty\FacultyResource
     *
     * @apiResourceModel App\Models\Faculty
     */
    public function store(StoreFacultyRequest $request)
    {
        $faculty = Faculty::create($request->validated());

        return new FacultyResource($faculty);
    }

    /**
     * Retrieve a faculty.
     *
     * @apiResource App\Http\Resources\Faculty\FacultyResource
     *
     * @apiResourceModel App\Models\Faculty
     */
    public function show(Faculty $faculty)
    {
        $faculty = QueryBuilder::for(Faculty::where('id', $faculty->id))
            ->allowedFields([
                'id',
                'code',
                'name',
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new FacultyResource($faculty);
    }

    /**
     * Update a faculty.
     *
     * @apiResource App\Http\Resources\Faculty\FacultyResource
     *
     * @apiResourceModel App\Models\Faculty
     */
    public function update(UpdateFacultyRequest $request, Faculty $faculty)
    {
        $faculty->update($request->validated());

        return new FacultyResource($faculty);
    }

    /**
     * Delete a faculty.
     *
     * @apiResource App\Http\Resources\Faculty\FacultyResource
     *
     * @apiResourceModel App\Models\Faculty
     */
    public function destroy(Faculty $faculty)
    {
        $faculty->delete();

        return new FacultyResource($faculty);
    }
}
