<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faculty\StoreFacultyRequest;
use App\Http\Requests\Faculty\UpdateFacultyRequest;
use App\Models\Faculty;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class FacultyController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(Faculty::class, 'faculty');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $faculties = QueryBuilder::for(Faculty::class)
            ->allowedFilters([
                'code',
                'name',
            ])
            ->defaultSorts([
                '-created_at',
                'id',
            ])
            ->allowedSorts([
                'id',
                'code',
                'name',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('faculties', $faculties);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFacultyRequest $request): JsonResponse
    {
        $faculty = Faculty::create($request->validated());

        return ResponseFormatter::singleton('faculty', $faculty, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Faculty $faculty): JsonResponse
    {
        return ResponseFormatter::singleton('faculty', $faculty);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFacultyRequest $request, Faculty $faculty): JsonResponse
    {
        $faculty->update($request->validated());

        return ResponseFormatter::singleton('faculty', $faculty);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faculty $faculty): JsonResponse
    {
        $faculty->delete();

        return ResponseFormatter::singleton('faculty', $faculty);
    }
}
