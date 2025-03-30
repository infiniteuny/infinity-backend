<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeamMember\StoreTeamMemberRequest;
use App\Http\Requests\TeamMember\UpdateTeamMemberRequest;
use App\Http\Resources\TeamMember\TeamMemberCollection;
use App\Http\Resources\TeamMember\TeamMemberResource;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Team Members
 * Manage team members.
 */
class TeamMemberController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(TeamMember::class, 'team_member');
    }

    /**
     * List all team members
     */
    public function index(Team $team, Request $request)
    {
        $teamMembers = QueryBuilder::for($team->members())
            ->cursorPaginate($request->query('per_page', 10));

        return new TeamMemberCollection($teamMembers);
    }

    /**
     * Create a team member
     */
    public function store(Team $team, StoreTeamMemberRequest $request)
    {
        $team->members()->attach($request->safe()->only('user_id'));

        $teamMember = $team
            ->members()
            ->wherePivot('user_id', $request->safe()->only('user_id'))
            ->first();

        return new TeamMemberResource($teamMember);
    }

    /**
     * Retrieve a team member
     */
    public function show(TeamMember $teamMember)
    {
        $teamMemberId = $teamMember->id;
        $teamMember = $teamMember
            ->team
            ->members()
            ->wherePivot('id', $teamMemberId);

        $teamMember = QueryBuilder::for($teamMember)
            ->firstOrFail();

        return new TeamMemberResource($teamMember);
    }

    /**
     * Update a team member
     */
    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember)
    {
        $teamMemberId = $teamMember->id;
        $team = $teamMember->team;

        $teamMember->update($request->validated());
        $teamMember = $team
            ->members()
            ->wherePivot('id', $teamMemberId)
            ->firstOrFail();

        return new TeamMemberResource($teamMember);
    }

    /**
     * Delete a team member
     */
    public function destroy(TeamMember $teamMember)
    {
        $teamMemberId = $teamMember->id;
        $team = $teamMember->team;
        $teamMember = $team
            ->members()
            ->wherePivot('id', $teamMemberId)
            ->firstOrFail();

        $team->members()->detach($teamMember->id);

        return new TeamMemberResource($teamMember);
    }
}
