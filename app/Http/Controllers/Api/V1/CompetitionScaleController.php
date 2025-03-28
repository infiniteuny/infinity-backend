<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionScale\StoreCompetitionScaleRequest;
use App\Http\Requests\CompetitionScale\UpdateCompetitionScaleRequest;
use App\Http\Resources\CompetitionScale\CompetitionScaleCollection;
use App\Http\Resources\CompetitionScale\CompetitionScaleResource;
use App\Models\CompetitionScale;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Competition Scales
 * Manage competition scales.
 */
class CompetitionScaleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CompetitionScale::class, 'competition_scale');
    }

    /**
     * List all competition scales
     *
     * @apiResourceCollection App\Http\Resources\CompetitionScale\CompetitionScaleCollection
     *
     * @apiResourceModel App\Models\CompetitionScale
     */
    public function index(Request $request)
    {
        $competitionScales = QueryBuilder::for(CompetitionScale::class)
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

        return new CompetitionScaleCollection($competitionScales);
    }

    /**
     * Create a competition scale
     *
     * @apiResource App\Http\Resources\CompetitionScale\CompetitionScaleResource
     *
     * @apiResourceModel App\Models\CompetitionScale
     */
    public function store(StoreCompetitionScaleRequest $request)
    {
        $competitionScale = CompetitionScale::create($request->validated());

        return new CompetitionScaleResource($competitionScale);
    }

    /**
     * Retrieve a competition scale
     *
     * @apiResource App\Http\Resources\CompetitionScale\CompetitionScaleResource
     *
     * @apiResourceModel App\Models\CompetitionScale
     */
    public function show(CompetitionScale $competitionScale)
    {
        $competitionScale = QueryBuilder::for(CompetitionScale::where('id', $competitionScale->id))
            ->allowedFields([
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new CompetitionScaleResource($competitionScale);
    }

    /**
     * Update a competition scale
     *
     * @apiResource App\Http\Resources\CompetitionScale\CompetitionScaleResource
     *
     * @apiResourceModel App\Models\CompetitionScale
     */
    public function update(UpdateCompetitionScaleRequest $request, CompetitionScale $competitionScale)
    {
        $competitionScale->update($request->validated());

        return new CompetitionScaleResource($competitionScale);
    }

    /**
     * Delete a competition scale
     *
     * @apiResource App\Http\Resources\CompetitionScale\CompetitionScaleResource
     *
     * @apiResourceModel App\Models\CompetitionScale
     */
    public function destroy(CompetitionScale $competitionScale)
    {
        $competitionScale->delete();

        return new CompetitionScaleResource($competitionScale);
    }
}
