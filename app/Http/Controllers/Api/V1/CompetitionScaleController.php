<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionScale\StoreCompetitionScaleRequest;
use App\Http\Requests\CompetitionScale\UpdateCompetitionScaleRequest;
use App\Models\CompetitionScale;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CompetitionScaleController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(CompetitionScale::class, 'competition_scale');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $competitionScales = QueryBuilder::for(CompetitionScale::class)
            ->allowedFilters([
                'name',
                AllowedFilter::exact('weight'),
            ])
            ->defaultSorts([
                'weight',
                'id',
            ])
            ->allowedSorts([
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('competition_scales', $competitionScales);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompetitionScaleRequest $request): JsonResponse
    {
        $competitionScale = CompetitionScale::create($request->validated());

        return ResponseFormatter::singleton('competition_scale', $competitionScale, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionScale $competitionScale): JsonResponse
    {
        return ResponseFormatter::singleton('competition_scale', $competitionScale);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionScaleRequest $request, CompetitionScale $competitionScale): JsonResponse
    {
        $competitionScale->update($request->validated());

        return ResponseFormatter::singleton('competition_scale', $competitionScale);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionScale $competitionScale): JsonResponse
    {
        $competitionScale->delete();

        return ResponseFormatter::singleton('competition_scale', $competitionScale);
    }
}
