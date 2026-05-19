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
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
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
                AllowedFilter::exact('sso_id', 'users.sso_id'),
                AllowedFilter::partial('name', 'users.name'),
                AllowedFilter::partial('email_address', 'users.email_address'),
                AllowedFilter::partial('phone_number', 'users.phone_number'),
                AllowedFilter::partial('student_id', 'users.student_id'),
                AllowedFilter::exact('major_id', 'users.major_id'),
                AllowedFilter::operator('start_date', FilterOperator::DYNAMIC, 'and', 'users.start_date'),
                AllowedFilter::operator('end_date', FilterOperator::DYNAMIC, 'and', 'users.end_date'),
                AllowedFilter::exact('is_member', 'users.is_member'),
                AllowedFilter::exact('is_extraordinary', 'users.is_extraordinary'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC, 'and', 'users.created_at'),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC, 'and', 'users.updated_at'),
            )
            ->allowedSorts(
                AllowedSort::field('id', 'users.id'),
                AllowedSort::field('name', 'users.name'),
                AllowedSort::field('email_address', 'users.email_address'),
                AllowedSort::field('phone_number', 'users.phone_number'),
                AllowedSort::field('student_id', 'users.student_id'),
                AllowedSort::field('major_id', 'users.major_id'),
                AllowedSort::field('start_date', 'users.start_date'),
                AllowedSort::field('end_date', 'users.end_date'),
                AllowedSort::field('is_member', 'users.is_member'),
                AllowedSort::field('is_extraordinary', 'users.is_extraordinary'),
                AllowedSort::field('created_at', 'users.created_at'),
                AllowedSort::field('updated_at', 'users.updated_at'),
            )
            ->defaultSorts('-users.id')
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
        $team->members()->attach($request->validated('user_id'));

        $teamMember = $team
            ->members()
            ->wherePivot('user_id', $request->validated('user_id'))
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

        if ($team->leader_id === $teamMember->user_id) {
            throw ValidationException::withMessages([
                'user_id' => ['The team leader cannot be modified. Please assign a new leader before updating this member.'],
            ]);
        }

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

        if ($team->leader_id === $teamMember->id) {
            throw ValidationException::withMessages([
                'user_id' => ['The team leader cannot be removed from the team. Please assign a new leader before removing this member.'],
            ]);
        }

        $team->members()->detach($teamMember->id);

        return new TeamMemberResource($teamMember);
    }
}
