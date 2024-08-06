<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionOrganizerType\StoreCompetitionOrganizerTypeRequest;
use App\Http\Requests\CompetitionOrganizerType\UpdateCompetitionOrganizerTypeRequest;
use App\Models\CompetitionOrganizerType;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CompetitionOrganizerTypeController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(CompetitionOrganizerType::class, 'competition_organizer_type');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $competitionOrganizerTypes = QueryBuilder::for(CompetitionOrganizerType::class)
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

        return ResponseFormatter::collection('competition_organizer_types', $competitionOrganizerTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompetitionOrganizerTypeRequest $request): JsonResponse
    {
        $competitionOrganizerType = CompetitionOrganizerType::create($request->validated());

        return ResponseFormatter::singleton('competition_organizer_type', $competitionOrganizerType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionOrganizerType $competitionOrganizerType): JsonResponse
    {
        return ResponseFormatter::singleton('competition_organizer_type', $competitionOrganizerType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionOrganizerTypeRequest $request, CompetitionOrganizerType $competitionOrganizerType): JsonResponse
    {
        $competitionOrganizerType->update($request->validated());

        return ResponseFormatter::singleton('competition_organizer_type', $competitionOrganizerType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionOrganizerType $competitionOrganizerType): JsonResponse
    {
        $competitionOrganizerType->delete();

        return ResponseFormatter::singleton('competition_organizer_type', $competitionOrganizerType);
    }
}
