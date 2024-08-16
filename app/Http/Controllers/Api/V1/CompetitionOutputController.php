<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionOutput\StoreCompetitionOutputRequest;
use App\Http\Requests\CompetitionOutput\UpdateCompetitionOutputRequest;
use App\Models\CompetitionOutput;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CompetitionOutputController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(CompetitionOutput::class, 'competition_output');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $competitionOutputs = QueryBuilder::for(CompetitionOutput::class)
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

        return ResponseFormatter::paginatedCollection('competition_outputs', $competitionOutputs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompetitionOutputRequest $request): JsonResponse
    {
        $competitionOutput = CompetitionOutput::create($request->validated());

        return ResponseFormatter::singleton('competition_output', $competitionOutput, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionOutput $competitionOutput): JsonResponse
    {
        return ResponseFormatter::singleton('competition_output', $competitionOutput);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionOutputRequest $request, CompetitionOutput $competitionOutput): JsonResponse
    {
        $competitionOutput->update($request->validated());

        return ResponseFormatter::singleton('competition_output', $competitionOutput);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionOutput $competitionOutput): JsonResponse
    {
        $competitionOutput->delete();

        return ResponseFormatter::singleton('competition_output', $competitionOutput);
    }
}
