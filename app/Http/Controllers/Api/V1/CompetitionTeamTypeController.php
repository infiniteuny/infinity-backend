<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionTeamType\StoreCompetitionTeamTypeRequest;
use App\Http\Requests\CompetitionTeamType\UpdateCompetitionTeamTypeRequest;
use App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeCollection;
use App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeResource;
use App\Models\CompetitionTeamType;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Competition Team Types
 * Manage competition team types.
 */
class CompetitionTeamTypeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CompetitionTeamType::class, 'competition_team_type');
    }

    /**
     * List all competition team types.
     *
     * @apiResourceCollection App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeCollection
     *
     * @apiResourceModel App\Models\CompetitionTeamType
     */
    public function index(Request $request)
    {
        $competitionTeamTypes = QueryBuilder::for(CompetitionTeamType::class)
            ->allowedFields([
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            ])
            ->allowedFilters([
                'name',
                AllowedFilter::exact('weight'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            ])
            ->defaultSorts([
                'weight',
                '-id',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new CompetitionTeamTypeCollection($competitionTeamTypes);
    }

    /**
     * Create a competition team type.
     *
     * @apiResource App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeResource
     *
     * @apiResourceModel App\Models\CompetitionTeamType
     */
    public function store(StoreCompetitionTeamTypeRequest $request)
    {
        $competitionTeamType = CompetitionTeamType::create($request->validated());

        return new CompetitionTeamTypeResource($competitionTeamType);
    }

    /**
     * Retrieve a competition team type.
     *
     * @apiResource App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeResource
     *
     * @apiResourceModel App\Models\CompetitionTeamType
     */
    public function show(CompetitionTeamType $competitionTeamType)
    {
        $competitionTeamType = QueryBuilder::for(CompetitionTeamType::where('id', $competitionTeamType->id))
            ->allowedFields([
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new CompetitionTeamTypeResource($competitionTeamType);
    }

    /**
     * Update a competition team type.
     *
     * @apiResource App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeResource
     *
     * @apiResourceModel App\Models\CompetitionTeamType
     */
    public function update(UpdateCompetitionTeamTypeRequest $request, CompetitionTeamType $competitionTeamType)
    {
        $competitionTeamType->update($request->validated());

        return new CompetitionTeamTypeResource($competitionTeamType);
    }

    /**
     * Delete a competition team type.
     *
     * @apiResource App\Http\Resources\CompetitionTeamType\CompetitionTeamTypeResource
     *
     * @apiResourceModel App\Models\CompetitionTeamType
     */
    public function destroy(CompetitionTeamType $competitionTeamType)
    {
        $competitionTeamType->delete();

        return new CompetitionTeamTypeResource($competitionTeamType);
    }
}
