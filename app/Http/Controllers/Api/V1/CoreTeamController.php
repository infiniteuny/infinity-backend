<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoreTeam\StoreCoreTeamRequest;
use App\Http\Requests\CoreTeam\UpdateCoreTeamRequest;
use App\Http\Resources\CoreTeam\CoreTeamCollection;
use App\Http\Resources\CoreTeam\CoreTeamResource;
use App\Models\CoreTeam;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Core Teams
 * Manage core teams.
 */
class CoreTeamController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CoreTeam::class, 'core_team');
    }

    /**
     * List all core teams
     *
     * @apiResourceCollection App\Http\Resources\CoreTeam\CoreTeamCollection
     *
     * @apiResourceModel App\Models\CoreTeam
     */
    public function index(Request $request)
    {
        $coreTeams = QueryBuilder::for(CoreTeam::class)
            ->allowedFields([
                'id',
                'year',
                'created_at',
                'updated_at',
            ])
            ->allowedFilters([
                AllowedFilter::exact('year'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'year',
                'created_at',
                'updated_at',
            ])
            ->defaultSorts([
                '-id',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new CoreTeamCollection($coreTeams);
    }

    /**
     * Create a core team
     *
     * @apiResource App\Http\Resources\CoreTeam\CoreTeamResource
     *
     * @apiResourceModel App\Models\CoreTeam
     */
    public function store(StoreCoreTeamRequest $request)
    {
        $coreTeam = CoreTeam::create($request->validated());

        return new CoreTeamResource($coreTeam);
    }

    /**
     * Retrieve a core team
     *
     * @apiResource App\Http\Resources\CoreTeam\CoreTeamResource
     *
     * @apiResourceModel App\Models\CoreTeam
     */
    public function show(CoreTeam $coreTeam)
    {
        $coreTeam = QueryBuilder::for(CoreTeam::where('id', $coreTeam->id))
            ->allowedFields([
                'id',
                'year',
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new CoreTeamResource($coreTeam);
    }

    /**
     * Update a core team
     *
     * @apiResource App\Http\Resources\CoreTeam\CoreTeamResource
     *
     * @apiResourceModel App\Models\CoreTeam
     */
    public function update(UpdateCoreTeamRequest $request, CoreTeam $coreTeam)
    {
        $coreTeam->update($request->validated());

        return new CoreTeamResource($coreTeam);
    }

    /**
     * Delete a core team
     *
     * @apiResource App\Http\Resources\CoreTeam\CoreTeamResource
     *
     * @apiResourceModel App\Models\CoreTeam
     */
    public function destroy(CoreTeam $coreTeam)
    {
        $coreTeam->delete();

        return new CoreTeamResource($coreTeam);
    }
}
