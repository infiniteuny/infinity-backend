<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\Team\TeamCollection;
use App\Http\Resources\Team\TeamResource;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * List all teams
     *
     * @apiResourceCollection App\Http\Resources\Team\TeamCollection
     *
     * @apiResourceModel App\Models\Team paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `leader_id`, `team_type_id`, `name`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `leader`, `members`, `team_type`, `fundApplications`, `achievements`. No-example
     * @queryParam filters[leader_id] string Filter by exact `leader_id`. No-example
     * @queryParam filters[team_type_id] string Filter by exact `team_type_id`. No-example
     * @queryParam filters[name] string Filter by partial `name`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `leader_id`, `team_type_id`, `name`, `created_at`, `updated_at`. Default: `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $user = Auth::guard()->user();
        $userId = $user->id;

        $teams = QueryBuilder::for(Team::class)
            ->allowedFields(
                'id',
                'leader_id',
                'team_type_id',
                'name',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes(
                'leader',
                'members',
                AllowedInclude::relationship('team_type', 'teamType'),
                AllowedInclude::relationship('fundApplications', 'fund_applications'),
                'achievements',
            )
            ->allowedFilters(
                AllowedFilter::exact('leader_id'),
                AllowedFilter::exact('team_type_id'),
                'name',
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'leader_id',
                'team_type_id',
                'name',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('-id');

        if (! $user->can('read-team')) {
            $teams = $teams
                ->where('leader_id', $userId)
                ->orWhereHas('members', function ($query) use ($userId) {
                    $query->where('users.id', $userId);
                });
        }

        $teams = $teams
            ->cursorPaginate($request->query('per_page', 10));

        return new TeamCollection($teams);
    }

    /**
     * Create a team
     *
     * @apiResource App\Http\Resources\Team\TeamResource status=201
     *
     * @apiResourceModel App\Models\Team
     */
    public function store(StoreTeamRequest $request)
    {
        DB::transaction(function () use ($request, &$team) {
            $team = Team::create($request->validated());

            $team->members()->attach($team->leader_id);
        });

        return new TeamResource($team);
    }

    /**
     * Retrieve a team
     *
     * @apiResource App\Http\Resources\Team\TeamResource
     *
     * @apiResourceModel App\Models\Team
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `leader_id`, `team_type_id`, `name`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `leader`, `members`, `team_type`, `fundApplications`, `achievements`. No-example
     */
    public function show(Team $team)
    {
        $team = QueryBuilder::for(Team::where('id', $team->id))
            ->allowedFields(
                'id',
                'leader_id',
                'team_type_id',
                'name',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes(
                'leader',
                'members',
                AllowedInclude::relationship('team_type', 'teamType'),
                AllowedInclude::relationship('fundApplications', 'fund_applications'),
                'achievements',
            )
            ->firstOrFail();

        return new TeamResource($team);
    }

    /**
     * Update a team
     *
     * @apiResource App\Http\Resources\Team\TeamResource
     *
     * @apiResourceModel App\Models\Team
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        $team->update($request->validated());

        return new TeamResource($team);
    }

    /**
     * Delete a team
     *
     * @apiResource App\Http\Resources\Team\TeamResource
     *
     * @apiResourceModel App\Models\Team
     */
    public function destroy(Team $team)
    {
        $team->delete();

        return new TeamResource($team);
    }
}
