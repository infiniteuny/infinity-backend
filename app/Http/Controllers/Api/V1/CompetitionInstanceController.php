<?php

namespace App\Http\Controllers\Api\V1;

use App\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionInstance\StoreCompetitionInstanceRequest;
use App\Http\Requests\CompetitionInstance\UpdateCompetitionInstanceRequest;
use App\Http\Resources\CompetitionInstance\CompetitionInstanceCollection;
use App\Http\Resources\CompetitionInstance\CompetitionInstanceResource;
use App\Jobs\DeleteBlob;
use App\Models\CompetitionInstance;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Competition Instances
 * Manage competition instances.
 */
class CompetitionInstanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,'.CompetitionInstance::class)->only('store');
        $this->middleware('can:update,competition_instance')->only('update');
        $this->middleware('can:delete,competition_instance')->only('destroy');
    }

    /**
     * List all competition instances
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\CompetitionInstance\CompetitionInstanceCollection
     *
     * @apiResourceModel App\Models\CompetitionInstance paginate=10,cursor
     */
    public function index(Request $request)
    {
        $competitionInstances = QueryBuilder::for(CompetitionInstance::class)
            ->allowedFields(
                'id',
                'competition_id',
                'name',
                'shortname',
                'description',
                'url',
                'organizer',
                'organizer_type_id',
                'logo',
                'start_date',
                'end_date',
                'location',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes(
                'competition',
                AllowedInclude::relationship('organizer_type', 'organizerType'),
            )
            ->allowedFilters(
                AllowedFilter::exact('competition_id'),
                AllowedFilter::groupOr('name', [
                    AllowedFilter::partial('name'),
                    AllowedFilter::partial('shortname'),
                ]),
                'description',
                'url',
                'organizer',
                AllowedFilter::exact('organizer_type_id'),
                AllowedFilter::operator('start_date', FilterOperator::DYNAMIC),
                AllowedFilter::operator('end_date', FilterOperator::DYNAMIC),
                'location',
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'competition_id',
                'name',
                'shortname',
                'organizer',
                'organizer_type_id',
                'start_date',
                'end_date',
                'location',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('-id')
            ->cursorPaginate($request->query('per_page', 10));

        return new CompetitionInstanceCollection($competitionInstances);
    }

    /**
     * Create a competition instance
     *
     * @apiResource App\Http\Resources\CompetitionInstance\CompetitionInstanceResource status=201
     *
     * @apiResourceModel App\Models\CompetitionInstance
     */
    public function store(StoreCompetitionInstanceRequest $request)
    {
        $manifest = Storage::store($request->file('logo'), 'competition-instances/logos');

        $competitionInstance = CompetitionInstance::create(
            array_replace($request->validated(), ['logo' => $manifest])
        );

        return new CompetitionInstanceResource($competitionInstance);
    }

    /**
     * Retrieve a competition instance
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\CompetitionInstance\CompetitionInstanceResource
     *
     * @apiResourceModel App\Models\CompetitionInstance
     */
    public function show(CompetitionInstance $competitionInstance)
    {
        $competitionInstance = QueryBuilder::for(CompetitionInstance::where('id', $competitionInstance->id))
            ->allowedFields(
                'id',
                'competition_id',
                'name',
                'shortname',
                'description',
                'url',
                'organizer',
                'organizer_type_id',
                'logo',
                'start_date',
                'end_date',
                'location',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes(
                'competition',
                AllowedInclude::relationship('organizer_type', 'organizerType'),
            )
            ->firstOrFail();

        return new CompetitionInstanceResource($competitionInstance);
    }

    /**
     * Update a competition instance
     *
     * @apiResource App\Http\Resources\CompetitionInstance\CompetitionInstanceResource
     *
     * @apiResourceModel App\Models\CompetitionInstance
     */
    public function update(UpdateCompetitionInstanceRequest $request, CompetitionInstance $competitionInstance)
    {
        $hasLogo = $request->has('logo');

        if ($hasLogo) {
            $oldManifest = $competitionInstance->getRawOriginal('logo');

            DeleteBlob::dispatch($oldManifest);

            $manifest = Storage::store($request->file('logo'), 'competition-instances/logos');
        }

        $competitionInstance->update(
            $hasLogo
                ? array_replace($request->validated(), ['logo' => $manifest])
                : $request->validated()
        );

        return new CompetitionInstanceResource($competitionInstance);
    }

    /**
     * Delete a competition instance
     *
     * @apiResource App\Http\Resources\CompetitionInstance\CompetitionInstanceResource
     *
     * @apiResourceModel App\Models\CompetitionInstance
     */
    public function destroy(CompetitionInstance $competitionInstance)
    {
        $manifest = $competitionInstance->getRawOriginal('logo');

        DeleteBlob::dispatch($manifest);

        $competitionInstance->delete();

        return new CompetitionInstanceResource($competitionInstance);
    }
}
