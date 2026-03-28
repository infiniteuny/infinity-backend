<?php

namespace App\Http\Controllers\Api\V1;

use App\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Competition\StoreCompetitionRequest;
use App\Http\Requests\Competition\UpdateCompetitionRequest;
use App\Http\Resources\Competition\CompetitionCollection;
use App\Http\Resources\Competition\CompetitionResource;
use App\Jobs\DeleteBlob;
use App\Models\Competition;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Competitions
 * Manage competitions.
 */
class CompetitionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,'.Competition::class)->only('store');
        $this->middleware('can:update,competition')->only('update');
        $this->middleware('can:delete,competition')->only('destroy');
    }

    /**
     * List all competitions
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\Competition\CompetitionCollection
     *
     * @apiResourceModel App\Models\Competition paginate=10,cursor
     */
    public function index(Request $request)
    {
        $competitions = QueryBuilder::for(Competition::class)
            ->allowedFields(
                'id',
                'name',
                'description',
                'url',
                'organizer',
                'organizer_type_id',
                'logo',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes(AllowedInclude::relationship('organizer_type', 'organizerType'))
            ->allowedFilters(
                'name',
                'description',
                'url',
                'organizer',
                AllowedFilter::exact('organizer_type_id'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'name',
                'organizer',
                'organizer_type_id',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('-id')
            ->cursorPaginate($request->query('per_page', 10));

        return new CompetitionCollection($competitions);
    }

    /**
     * Create a competition
     *
     * @apiResource App\Http\Resources\Competition\CompetitionResource status=201
     *
     * @apiResourceModel App\Models\Competition
     */
    public function store(StoreCompetitionRequest $request)
    {
        $manifest = Storage::store($request->file('logo'), 'competitions/logos');

        $competition = Competition::create(
            array_replace($request->validated(), ['logo' => $manifest])
        );

        return new CompetitionResource($competition);
    }

    /**
     * Retrieve a competition
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\Competition\CompetitionResource
     *
     * @apiResourceModel App\Models\Competition
     */
    public function show(Competition $competition)
    {
        $competition = QueryBuilder::for(Competition::where('id', $competition->id))
            ->allowedFields(
                'id',
                'name',
                'description',
                'url',
                'organizer',
                'organizer_type_id',
                'logo',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes(AllowedInclude::relationship('organizer_type', 'organizerType'))
            ->firstOrFail();

        return new CompetitionResource($competition);
    }

    /**
     * Update a competition
     *
     * @apiResource App\Http\Resources\Competition\CompetitionResource
     *
     * @apiResourceModel App\Models\Competition
     */
    public function update(UpdateCompetitionRequest $request, Competition $competition)
    {
        $hasLogo = $request->has('logo');

        if ($hasLogo) {
            $oldManifest = $competition->getRawOriginal('logo');

            DeleteBlob::dispatch($oldManifest);

            $manifest = Storage::store($request->file('logo'), 'competitions/logos');
        }

        $competition->update(
            $hasLogo
                ? array_replace($request->validated(), ['logo' => $manifest])
                : $request->validated()
        );

        return new CompetitionResource($competition);
    }

    /**
     * Delete a competition
     *
     * @apiResource App\Http\Resources\Competition\CompetitionResource
     *
     * @apiResourceModel App\Models\Competition
     */
    public function destroy(Competition $competition)
    {
        $manifest = $competition->getRawOriginal('logo');

        DeleteBlob::dispatch($manifest);

        $competition->delete();

        return new CompetitionResource($competition);
    }
}
