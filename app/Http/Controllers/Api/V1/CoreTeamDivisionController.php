<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoreTeamDivision\StoreCoreTeamDivisionRequest;
use App\Http\Requests\CoreTeamDivision\UpdateCoreTeamDivisionRequest;
use App\Models\CoreTeamDivision;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CoreTeamDivisionController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(CoreTeamDivision::class, 'core_team_division');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $coreTeamDivisions = QueryBuilder::for(CoreTeamDivision::class)
            ->allowedFilters([
                'name',
                'priority',
            ])
            ->defaultSorts([
                '-created_at',
                'id',
            ])
            ->allowedSorts([
                'id',
                'name',
                'priority',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('core_team_divisions', $coreTeamDivisions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCoreTeamDivisionRequest $request): JsonResponse
    {
        $coreTeamDivision = CoreTeamDivision::create($request->validated());

        return ResponseFormatter::singleton('core_team_division', $coreTeamDivision, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CoreTeamDivision $coreTeamDivision): JsonResponse
    {
        return ResponseFormatter::singleton('core_team_division', $coreTeamDivision);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoreTeamDivisionRequest $request, CoreTeamDivision $coreTeamDivision): JsonResponse
    {
        $coreTeamDivision->update($request->validated());

        return ResponseFormatter::singleton('core_team_division', $coreTeamDivision);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoreTeamDivision $coreTeamDivision): JsonResponse
    {
        $coreTeamDivision->delete();

        return ResponseFormatter::singleton('core_team_division', $coreTeamDivision);
    }
}
