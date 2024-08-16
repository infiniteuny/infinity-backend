<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoreTeam\StoreCoreTeamRequest;
use App\Http\Requests\CoreTeam\UpdateCoreTeamRequest;
use App\Models\CoreTeam;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CoreTeamController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(CoreTeam::class, 'coreTeam');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $coreTeams = QueryBuilder::for(CoreTeam::class)
            ->allowedFilters([
                'year',
            ])
            ->defaultSorts([
                '-created_at',
                'id',
            ])
            ->allowedSorts([
                'id',
                'year',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('core_teams', $coreTeams);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCoreTeamRequest $request): JsonResponse
    {
        $coreTeam = CoreTeam::create($request->validated());

        return ResponseFormatter::singleton('core_team', $coreTeam, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CoreTeam $coreTeam): JsonResponse
    {
        return ResponseFormatter::singleton('coreTeam', $coreTeam);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoreTeamRequest $request, CoreTeam $coreTeam): JsonResponse
    {
        $coreTeam->update($request->validated());

        return ResponseFormatter::singleton('coreTeam', $coreTeam);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoreTeam $coreTeam): JsonResponse
    {
        $coreTeam->delete();

        return ResponseFormatter::singleton('coreTeam', $coreTeam);
    }
}
