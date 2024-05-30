<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionRank\StoreCompetitionRankRequest;
use App\Http\Requests\CompetitionRank\UpdateCompetitionRankRequest;
use App\Models\CompetitionRank;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CompetitionRankController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(CompetitionRank::class, 'competition_rank');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $competitionRanks = QueryBuilder::for(CompetitionRank::class)
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

        return ResponseFormatter::collection('competition_ranks', $competitionRanks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompetitionRankRequest $request): JsonResponse
    {
        $competitionRank = CompetitionRank::create($request->validated());

        return ResponseFormatter::singleton('competition_rank', $competitionRank, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionRank $competitionRank): JsonResponse
    {
        return ResponseFormatter::singleton('competition_rank', $competitionRank);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionRankRequest $request, CompetitionRank $competitionRank): JsonResponse
    {
        $competitionRank->update($request->validated());

        return ResponseFormatter::singleton('competition_rank', $competitionRank);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionRank $competitionRank): JsonResponse
    {
        $competitionRank->delete();

        return ResponseFormatter::singleton('competition_rank', $competitionRank);
    }
}
