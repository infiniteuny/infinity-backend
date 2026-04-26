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
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Team Members
 * Manage team members.
 */
class TeamMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:viewAny,'.TeamMember::class.',team')->only('index');
        $this->middleware('can:view,team_member')->only('show');
        $this->middleware('can:create,'.TeamMember::class.',team')->only('store');
        $this->middleware('can:update,team_member')->only('update');
        $this->middleware('can:delete,team_member')->only('destroy');
    }

    /**
     * List all team members
     *
     * @apiResourceCollection App\Http\Resources\TeamMember\TeamMemberCollection
     *
     * @apiResourceModel App\Models\User states=pivotTeamMember paginate=10,cursor
     */
    public function index(Team $team, Request $request)
    {
        $teamMembers = QueryBuilder::for($team->members())
            ->allowedIncludes(
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
            )
            ->allowedFilters(
                AllowedFilter::exact('sso_id'),
                'name',
                'email_address',
                'phone_number',
                'student_id',
                AllowedFilter::exact('major_id'),
                AllowedFilter::operator('start_date', FilterOperator::DYNAMIC),
                AllowedFilter::operator('end_date', FilterOperator::DYNAMIC),
                AllowedFilter::exact('is_member'),
                AllowedFilter::exact('is_extraordinary'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'name',
                'email_address',
                'phone_number',
                'student_id',
                'major_id',
                'start_date',
                'end_date',
                'is_member',
                'is_extraordinary',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('-id')
            ->cursorPaginate($request->query('per_page', 10));

        return new TeamMemberCollection($teamMembers);
    }

    /**
     * Create a team member
     *
     * @apiResource App\Http\Resources\TeamMember\TeamMemberResource status=201
     *
     * @apiResourceModel App\Models\User states=pivotTeamMember
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
     *
     * @apiResource App\Http\Resources\TeamMember\TeamMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotTeamMember
     */
    public function show(TeamMember $teamMember)
    {
        $teamMemberId = $teamMember->id;
        $teamMember = $teamMember
            ->team
            ->members()
            ->wherePivot('id', $teamMemberId);

        $teamMember = QueryBuilder::for($teamMember)
            ->allowedIncludes(
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
            )
            ->firstOrFail();

        return new TeamMemberResource($teamMember);
    }

    /**
     * Update a team member
     *
     * @apiResource App\Http\Resources\TeamMember\TeamMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotTeamMember
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
     *
     * @apiResource App\Http\Resources\TeamMember\TeamMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotTeamMember
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
