<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionRank\StoreCompetitionRankRequest;
use App\Http\Requests\CompetitionRank\UpdateCompetitionRankRequest;
use App\Http\Resources\CompetitionRank\CompetitionRankCollection;
use App\Http\Resources\CompetitionRank\CompetitionRankResource;
use App\Models\CompetitionRank;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Competition Ranks
 * Manage competition ranks.
 */
class CompetitionRankController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CompetitionRank::class, 'competition_rank');
    }

    /**
     * List all competition ranks
     *
     * @apiResourceCollection App\Http\Resources\CompetitionRank\CompetitionRankCollection
     *
     * @apiResourceModel App\Models\CompetitionRank paginate=10,cursor
     */
    public function index(Request $request)
    {
        $competitionRanks = QueryBuilder::for(CompetitionRank::class)
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

        return new CompetitionRankCollection($competitionRanks);
    }

    /**
     * Create a competition rank
     *
     * @apiResource App\Http\Resources\CompetitionRank\CompetitionRankResource status=201
     *
     * @apiResourceModel App\Models\CompetitionRank
     */
    public function store(StoreCompetitionRankRequest $request)
    {
        $competitionRank = CompetitionRank::create($request->validated());

        return new CompetitionRankResource($competitionRank);
    }

    /**
     * Retrieve a competition rank
     *
     * @apiResource App\Http\Resources\CompetitionRank\CompetitionRankResource
     *
     * @apiResourceModel App\Models\CompetitionRank
     */
    public function show(CompetitionRank $competitionRank)
    {
        $competitionRank = QueryBuilder::for(CompetitionRank::where('id', $competitionRank->id))
            ->allowedFields([
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new CompetitionRankResource($competitionRank);
    }

    /**
     * Update a competition rank
     *
     * @apiResource App\Http\Resources\CompetitionRank\CompetitionRankResource
     *
     * @apiResourceModel App\Models\CompetitionRank
     */
    public function update(UpdateCompetitionRankRequest $request, CompetitionRank $competitionRank)
    {
        $competitionRank->update($request->validated());

        return new CompetitionRankResource($competitionRank);
    }

    /**
     * Delete a competition rank
     *
     * @apiResource App\Http\Resources\CompetitionRank\CompetitionRankResource
     *
     * @apiResourceModel App\Models\CompetitionRank
     */
    public function destroy(CompetitionRank $competitionRank)
    {
        $competitionRank->delete();

        return new CompetitionRankResource($competitionRank);
    }
}
