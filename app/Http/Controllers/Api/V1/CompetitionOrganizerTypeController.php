<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionOrganizerType\StoreCompetitionOrganizerTypeRequest;
use App\Http\Requests\CompetitionOrganizerType\UpdateCompetitionOrganizerTypeRequest;
use App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeCollection;
use App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeResource;
use App\Models\CompetitionOrganizerType;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Competition Organizer Types
 * Manage competition organizer types.
 */
class CompetitionOrganizerTypeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CompetitionOrganizerType::class, 'competition_organizer_type');
    }

    /**
     * List all competition organizer types
     *
     * @apiResourceCollection App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeCollection
     *
     * @apiResourceModel App\Models\CompetitionOrganizerType paginate=10,cursor
     */
    public function index(Request $request)
    {
        $competitionOrganizerTypes = QueryBuilder::for(CompetitionOrganizerType::class)
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

        return new CompetitionOrganizerTypeCollection($competitionOrganizerTypes);
    }

    /**
     * Create a competition organizer type
     *
     * @apiResource App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeResource status=201
     *
     * @apiResourceModel App\Models\CompetitionOrganizerType
     */
    public function store(StoreCompetitionOrganizerTypeRequest $request)
    {
        $competitionOrganizerType = CompetitionOrganizerType::create($request->validated());

        return new CompetitionOrganizerTypeResource($competitionOrganizerType);
    }

    /**
     * Retrieve a competition organizer type
     *
     * @apiResource App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeResource
     *
     * @apiResourceModel App\Models\CompetitionOrganizerType
     */
    public function show(CompetitionOrganizerType $competitionOrganizerType)
    {
        $competitionOrganizerType = QueryBuilder::for(CompetitionOrganizerType::where('id', $competitionOrganizerType->id))
            ->allowedFields([
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new CompetitionOrganizerTypeResource($competitionOrganizerType);
    }

    /**
     * Update a competition organizer type
     *
     * @apiResource App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeResource
     *
     * @apiResourceModel App\Models\CompetitionOrganizerType
     */
    public function update(UpdateCompetitionOrganizerTypeRequest $request, CompetitionOrganizerType $competitionOrganizerType)
    {
        $competitionOrganizerType->update($request->validated());

        return new CompetitionOrganizerTypeResource($competitionOrganizerType);
    }

    /**
     * Delete a competition organizer type
     *
     * @apiResource App\Http\Resources\CompetitionOrganizerType\CompetitionOrganizerTypeResource
     *
     * @apiResourceModel App\Models\CompetitionOrganizerType
     */
    public function destroy(CompetitionOrganizerType $competitionOrganizerType)
    {
        $competitionOrganizerType->delete();

        return new CompetitionOrganizerTypeResource($competitionOrganizerType);
    }
}
