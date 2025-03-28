<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionOutput\StoreCompetitionOutputRequest;
use App\Http\Requests\CompetitionOutput\UpdateCompetitionOutputRequest;
use App\Http\Resources\CompetitionOutput\CompetitionOutputCollection;
use App\Http\Resources\CompetitionOutput\CompetitionOutputResource;
use App\Models\CompetitionOutput;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Competition Outputs
 * Manage competition outputs.
 */
class CompetitionOutputController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CompetitionOutput::class, 'competition_output');
    }

    /**
     * List all competition outputs
     *
     * @apiResourceCollection App\Http\Resources\CompetitionOutput\CompetitionOutputCollection
     *
     * @apiResourceModel App\Models\CompetitionOutput
     */
    public function index(Request $request)
    {
        $competitionOutputs = QueryBuilder::for(CompetitionOutput::class)
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

        return new CompetitionOutputCollection($competitionOutputs);
    }

    /**
     * Create a competition output
     *
     * @apiResource App\Http\Resources\CompetitionOutput\CompetitionOutputResource
     *
     * @apiResourceModel App\Models\CompetitionOutput
     */
    public function store(StoreCompetitionOutputRequest $request)
    {
        $competitionOutput = CompetitionOutput::create($request->validated());

        return new CompetitionOutputResource($competitionOutput);
    }

    /**
     * Retrieve a competition output
     *
     * @apiResource App\Http\Resources\CompetitionOutput\CompetitionOutputResource
     *
     * @apiResourceModel App\Models\CompetitionOutput
     */
    public function show(CompetitionOutput $competitionOutput)
    {
        $competitionOutput = QueryBuilder::for(CompetitionOutput::where('id', $competitionOutput->id))
            ->allowedFields([
                'id',
                'name',
                'weight',
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new CompetitionOutputResource($competitionOutput);
    }

    /**
     * Update a competition output
     *
     * @apiResource App\Http\Resources\CompetitionOutput\CompetitionOutputResource
     *
     * @apiResourceModel App\Models\CompetitionOutput
     */
    public function update(UpdateCompetitionOutputRequest $request, CompetitionOutput $competitionOutput)
    {
        $competitionOutput->update($request->validated());

        return new CompetitionOutputResource($competitionOutput);
    }

    /**
     * Delete a competition output
     *
     * @apiResource App\Http\Resources\CompetitionOutput\CompetitionOutputResource
     *
     * @apiResourceModel App\Models\CompetitionOutput
     */
    public function destroy(CompetitionOutput $competitionOutput)
    {
        $competitionOutput->delete();

        return new CompetitionOutputResource($competitionOutput);
    }
}
