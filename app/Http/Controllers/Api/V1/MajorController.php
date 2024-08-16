<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Major\StoreMajorRequest;
use App\Http\Requests\Major\UpdateMajorRequest;
use App\Models\Major;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MajorController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(Major::class, 'major');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $majors = QueryBuilder::for(Major::class)
            ->allowedFilters([
                AllowedFilter::exact('code'),
                'name',
                AllowedFilter::exact('degree_id'),
                AllowedFilter::exact('faculty_id'),
            ])
            ->defaultSorts([
                'code',
                'id',
            ])
            ->allowedSorts([
                'id',
                'code',
                'name',
                'degree_id',
                'faculty_id',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('majors', $majors);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMajorRequest $request): JsonResponse
    {
        $major = Major::create($request->validated());

        return ResponseFormatter::singleton('major', $major, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Major $major): JsonResponse
    {
        return ResponseFormatter::singleton('major', $major);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMajorRequest $request, Major $major): JsonResponse
    {
        $major->update($request->validated());

        return ResponseFormatter::singleton('major', $major);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Major $major): JsonResponse
    {
        $major->delete();

        return ResponseFormatter::singleton('major', $major);
    }
}
