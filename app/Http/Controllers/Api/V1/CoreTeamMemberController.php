<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoreTeamMember\StoreCoreTeamMemberRequest;
use App\Http\Requests\CoreTeamMember\UpdateCoreTeamMemberRequest;
use App\Models\CoreTeamMember;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CoreTeamMemberController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(Degree::class, 'Degree');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $coreTeamMembers = QueryBuilder::for(CoreTeamMember::class)
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

        return ResponseFormatter::collection('core_team_members', $coreTeamMembers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCoreTeamMemberRequest $request): JsonResponse
    {
        $coreTeamMember = CoreTeamMember::create($request->validated());

        return ResponseFormatter::singleton('core_team_member', $coreTeamMember, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CoreTeamMember $coreTeamMember): JsonResponse
    {
        return ResponseFormatter::singleton('core_team_member', $coreTeamMember);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoreTeamMemberRequest $request, CoreTeamMember $coreTeamMember): JsonResponse
    {
        $coreTeamMember->update($request->validated());

        return ResponseFormatter::singleton('core_team_member', $coreTeamMember);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoreTeamMember $coreTeamMember): JsonResponse
    {
        $coreTeamMember->delete();

        return ResponseFormatter::singleton('core_team_member', $coreTeamMember);
    }
}
