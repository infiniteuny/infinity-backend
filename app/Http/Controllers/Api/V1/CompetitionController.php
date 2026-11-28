<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Competition\StoreCompetitionRequest;
use App\Http\Requests\Competition\UpdateCompetitionRequest;
use App\Http\Resources\Competition\CompetitionCollection;
use App\Http\Resources\Competition\CompetitionResource;
use App\Models\Competition;
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Competitions
 * Manage competitions.
 */
class CompetitionController extends Controller
{
    use HasPagination;

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
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `shortname`, `created_at`, `updated_at`. No-example
     * @queryParam filters[name] string Filter by partial `name` or `shortname`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `name`, `shortname`, `created_at`, `updated_at`. Default: `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $query = QueryBuilder::for(Competition::class)
            ->allowedFields(
                'id',
                'name',
                'shortname',
                'created_at',
                'updated_at',
            )
            ->allowedFilters(
                AllowedFilter::groupOr('name', [
                    AllowedFilter::partial('name'),
                    AllowedFilter::partial('shortname'),
                ]),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'name',
                'shortname',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('-id');

        $competitions = $this->cursorPaginateWithTieBreaker($query, $request);

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
        $competition = Competition::create($request->validated());

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
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `shortname`, `created_at`, `updated_at`. No-example
     */
    public function show(Competition $competition)
    {
        $competition = QueryBuilder::for(Competition::where('id', $competition->id))
            ->allowedFields(
                'id',
                'name',
                'shortname',
                'created_at',
                'updated_at',
            )
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
        $competition->update($request->validated());

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
        $competition->delete();

        return new CompetitionResource($competition);
    }
}
