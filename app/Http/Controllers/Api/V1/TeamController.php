<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\Team\TeamCollection;
use App\Http\Resources\Team\TeamResource;
use App\Models\Team;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Teams
 * Manage teams.
 */
class TeamController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Team::class, 'team');
    }

    /**
     * List all teams.
     *
     * @apiResourceCollection App\Http\Resources\Team\TeamCollection
     *
     * @apiResourceModel App\Models\Team
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $teams = QueryBuilder::for(Team::class)
            ->allowedFields([
                'id',
                'leader_id',
                'name',
                'is_personal',
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes([
                'leader',
                'members',
                AllowedInclude::relationship('fundApplications', 'fund_applications'),
                'achievements',
            ])
            ->allowedFilters([
                AllowedFilter::exact('leader_id'),
                'name',
                AllowedFilter::exact('is_personal'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'leader_id',
                'name',
                'is_personal',
                'created_at',
                'updated_at',
            ])
            ->defaultSorts([
                '-id',
            ]);

        if ($request->user()->can('read-team')) {
            $teams = $teams;
        } else {
            $teams = $teams
                ->where('leader_id', $userId)
                ->orWhereHas('members', function ($query) use ($userId) {
                    $query->where('id', $userId);
                });
        }

        $teams = $teams
            ->cursorPaginate($request->query('per_page', 10));

        return new TeamCollection($teams);
    }

    /**
     * Create a team.
     *
     * @apiResource App\Http\Resources\Team\TeamResource
     *
     * @apiResourceModel App\Models\Team
     */
    public function store(StoreTeamRequest $request)
    {
        $team = Team::create($request->validated());

        return new TeamResource($team);
    }

    /**
     * Retrieve a team.
     *
     * @apiResource App\Http\Resources\Team\TeamResource
     *
     * @apiResourceModel App\Models\Team
     */
    public function show(Team $team)
    {
        $team = QueryBuilder::for(Team::where('id', $team->id))
            ->allowedFields([
                'id',
                'leader_id',
                'name',
                'is_personal',
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes([
                'leader',
                'members',
                AllowedInclude::relationship('fundApplications', 'fund_applications'),
                'achievements',
            ])
            ->firstOrFail();

        return new TeamResource($team);
    }

    /**
     * Update a team.
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        $team->update($request->validated());

        return new TeamResource($team);
    }

    /**
     * Delete a team.
     */
    public function destroy(Team $team)
    {
        $team->delete();

        return new TeamResource($team);
    }
}
