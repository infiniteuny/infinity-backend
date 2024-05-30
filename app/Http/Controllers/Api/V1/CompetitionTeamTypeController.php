<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionTeamType\StoreCompetitionTeamTypeRequest;
use App\Http\Requests\CompetitionTeamType\UpdateCompetitionTeamTypeRequest;
use App\Models\CompetitionTeamType;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CompetitionTeamTypeController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(CompetitionTeamType::class, 'competition_team_type');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $competitionTeamTypes = QueryBuilder::for(CompetitionTeamType::class)
            ->allowedFilters([
                'name',
                'weight',
            ])
            ->defaultSorts([
                '-created_at',
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

        return ResponseFormatter::collection('competition_team_types', $competitionTeamTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompetitionTeamTypeRequest $request): JsonResponse
    {
        $competitionTeamType = CompetitionTeamType::create($request->validated());

        return ResponseFormatter::singleton('competition_team_type', $competitionTeamType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionTeamType $competitionTeamType): JsonResponse
    {
        return ResponseFormatter::singleton('competition_team_type', $competitionTeamType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionTeamTypeRequest $request, CompetitionTeamType $competitionTeamType): JsonResponse
    {
        $competitionTeamType->update($request->validated());

        return ResponseFormatter::singleton('competition_team_type', $competitionTeamType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionTeamType $competitionTeamType): JsonResponse
    {
        $competitionTeamType->delete();

        return ResponseFormatter::singleton('competition_team_type', $competitionTeamType);
    }
}
