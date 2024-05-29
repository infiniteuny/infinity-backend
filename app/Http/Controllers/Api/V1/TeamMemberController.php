<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeamMember\StoreTeamMemberRequest;
use App\Http\Requests\TeamMember\UpdateTeamMemberRequest;
use App\Models\TeamMember;
use App\Utils\ResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class TeamMemberController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(TeamMember::class, 'team_member');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $teamMembers = QueryBuilder::for(TeamMember::class)
            ->allowedFilters(['team_id', 'member_id'])
            ->defaultSorts(['-created_at', 'id'])
            ->allowedSorts(['id', 'team_id', 'member_id', 'created_at', 'updated_at'])
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::collection('team_members', $teamMembers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamMemberRequest $request): JsonResponse
    {
        $teamMember = TeamMember::create($request->validated());

        return ResponseFormatter::singleton('team_member', $teamMember, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TeamMember $teamMember): JsonResponse
    {
        return ResponseFormatter::singleton('team_member', $teamMember);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): JsonResponse
    {
        $teamMember->update($request->validated());

        return ResponseFormatter::singleton('team_member', $teamMember);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeamMember $teamMember): JsonResponse
    {
        $teamMember->delete();

        return ResponseFormatter::singleton('team_member', $teamMember);
    }
}
