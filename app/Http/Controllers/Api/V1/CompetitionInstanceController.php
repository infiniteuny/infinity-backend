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
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

/**
 * @group Competition Instances
 * Manage competition instances.
 */
class CompetitionInstanceController extends Controller
{
    use HasPagination;

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
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `competition_id`, `name`, `shortname`, `description`, `url`, `organizer`, `organizer_type_id`, `logo`, `start_date`, `end_date`, `location`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `competition`, `organizer_type`. No-example
     * @queryParam filters[competition_id] string Filter by exact `competition_id`. No-example
     * @queryParam filters[name] string Filter by partial `name` or `shortname`. No-example
     * @queryParam filters[description] string Filter by partial `description`. No-example
     * @queryParam filters[url] string Filter by partial `url`. No-example
     * @queryParam filters[organizer] string Filter by partial `organizer`. No-example
     * @queryParam filters[organizer_type_id] string Filter by exact `organizer_type_id`. No-example
     * @queryParam filters[start_date] string Filter by `start_date` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[end_date] string Filter by `end_date` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[location] string Filter by partial `location`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `competition_id`, `name`, `shortname`, `organizer`, `organizer_type_id`, `start_date`, `end_date`, `location`, `created_at`, `updated_at`. Default: `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
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
            ->cursorPaginate($this->resolvePerPage($request));

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

        try {
            $competitionInstance = CompetitionInstance::create(
                array_replace($request->validated(), ['logo' => $manifest])
            );
        } catch (Throwable $e) {
            if (is_string($manifest) && $manifest !== '') {
                DeleteBlob::dispatch($manifest);
            }

            throw $e;
        }

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
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `competition_id`, `name`, `shortname`, `description`, `url`, `organizer`, `organizer_type_id`, `logo`, `start_date`, `end_date`, `location`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `competition`, `organizer_type`. No-example
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
        $oldManifest = null;
        $manifest = null;

        if ($hasLogo) {
            $oldManifest = $competitionInstance->getRawOriginal('logo');
            $manifest = Storage::store($request->file('logo'), 'competition-instances/logos');
        }

        try {
            $competitionInstance->update(
                $hasLogo
                    ? array_replace($request->validated(), ['logo' => $manifest])
                    : $request->validated()
            );
        } catch (Throwable $e) {
            if (is_string($manifest) && $manifest !== '') {
                DeleteBlob::dispatch($manifest);
            }

            throw $e;
        }

        if (is_string($oldManifest) && $oldManifest !== '') {
            DeleteBlob::dispatch($oldManifest);
        }

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

        $competitionInstance->delete();

        if (is_string($manifest) && $manifest !== '') {
            DeleteBlob::dispatch($manifest);
        }

        return new CompetitionInstanceResource($competitionInstance);
    }
}
