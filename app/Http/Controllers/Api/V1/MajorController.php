<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Major\StoreMajorRequest;
use App\Http\Requests\Major\UpdateMajorRequest;
use App\Http\Resources\Major\MajorCollection;
use App\Http\Resources\Major\MajorResource;
use App\Models\Major;
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
    public function __construct()
    {
        $this->authorizeResource(Major::class, 'major');
    }

    /**
     * List all majors.
     *
     * @apiResourceCollection App\Http\Resources\Major\MajorCollection
     *
     * @apiResourceModel App\Models\Major
     */
    public function index(Request $request)
    {
        $majors = QueryBuilder::for(Major::class)
            ->allowedFields([
                'id',
                'degree_id',
                'faculty_id',
                'code',
                'name',
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes([
                'degree',
                'faculty',
            ])
            ->allowedFilters([
                AllowedFilter::exact('degree_id'),
                AllowedFilter::exact('faculty_id'),
                AllowedFilter::exact('code'),
                'name',
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'degree_id',
                'faculty_id',
                'code',
                'name',
                'created_at',
                'updated_at',
            ])
            ->defaultSorts([
                'code',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new MajorCollection($majors);
    }

    /**
     * Create a major.
     *
     * @apiResource App\Http\Resources\Major\MajorResource
     *
     * @apiResourceModel App\Models\Major
     */
    public function store(StoreMajorRequest $request)
    {
        $major = Major::create($request->validated());

        return new MajorResource($major);
    }

    /**
     * Retrieve a major.
     *
     * @apiResource App\Http\Resources\Major\MajorResource
     *
     * @apiResourceModel App\Models\Major
     */
    public function show(Major $major)
    {
        $major = QueryBuilder::for(Major::where('id', $major->id))
            ->allowedFields([
                'id',
                'degree_id',
                'faculty_id',
                'code',
                'name',
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes([
                'degree',
                'faculty',
            ])
            ->firstOrFail();

        return new MajorResource($major);
    }

    /**
     * Update a major.
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
     * Delete a major.
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
