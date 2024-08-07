<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionTimeRange\StoreCompetitionTimeRangeRequest;
use App\Http\Requests\CompetitionTimeRange\UpdateCompetitionTimeRangeRequest;
use App\Models\CompetitionTimeRange;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CompetitionTimeRangeController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(CompetitionTimeRange::class, 'competition_time_range');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $competitionTimeRanges = QueryBuilder::for(CompetitionTimeRange::class)
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

        return ResponseFormatter::collection('competition_time_ranges', $competitionTimeRanges);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompetitionTimeRangeRequest $request): JsonResponse
    {
        $competitionTimeRange = CompetitionTimeRange::create($request->validated());

        return ResponseFormatter::singleton('competition_time_range', $competitionTimeRange, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionTimeRange $competitionTimeRange): JsonResponse
    {
        return ResponseFormatter::singleton('competition_time_range', $competitionTimeRange);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionTimeRangeRequest $request, CompetitionTimeRange $competitionTimeRange): JsonResponse
    {
        $competitionTimeRange->update($request->validated());

        return ResponseFormatter::singleton('competition_time_range', $competitionTimeRange);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionTimeRange $competitionTimeRange): JsonResponse
    {
        $competitionTimeRange->delete();

        return ResponseFormatter::singleton('competition_time_range', $competitionTimeRange);
    }
}
