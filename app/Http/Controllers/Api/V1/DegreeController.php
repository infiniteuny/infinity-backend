<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Degree\StoreDegreeRequest;
use App\Http\Requests\Degree\UpdateDegreeRequest;
use App\Models\Degree;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DegreeController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(Degree::class, 'degree');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $degrees = QueryBuilder::for(Degree::class)
            ->allowedFilters([
                AllowedFilter::exact('code'),
                'name',
            ])
            ->defaultSorts([
                'code',
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

        return ResponseFormatter::paginatedCollection('degrees', $degrees);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDegreeRequest $request): JsonResponse
    {
        $degree = Degree::create($request->validated());

        return ResponseFormatter::singleton('degree', $degree, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Degree $degree): JsonResponse
    {
        return ResponseFormatter::singleton('degree', $degree);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDegreeRequest $request, Degree $degree): JsonResponse
    {
        $degree->update($request->validated());

        return ResponseFormatter::singleton('degree', $degree);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Degree $degree): JsonResponse
    {
        $degree->delete();

        return ResponseFormatter::singleton('degree', $degree);
    }
}
