<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoreTeam\StoreCoreTeamRequest;
use App\Http\Requests\CoreTeam\UpdateCoreTeamRequest;
use App\Http\Resources\CoreTeam\CoreTeamCollection;
use App\Http\Resources\CoreTeam\CoreTeamResource;
use App\Models\CoreTeam;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $this->middleware('can:create,'.CoreTeam::class)->only('store');
        $this->middleware('can:update,core_team')->only('update');
        $this->middleware('can:delete,core_team')->only('destroy');
    }

    /**
     * List all core teams
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\CoreTeam\CoreTeamCollection
     *
     * @apiResourceModel App\Models\CoreTeam paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `year`, `is_active`, `created_at`, `updated_at`. No-example
     * @queryParam filters[year] integer Filter by exact `year`. No-example
     * @queryParam filters[is_active] boolean Filter by exact `is_active`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `year`, `is_active`, `created_at`, `updated_at`. Default: `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $coreTeams = QueryBuilder::for(CoreTeam::class)
            ->allowedFields(
                'id',
                'year',
                'is_active',
                'created_at',
                'updated_at',
            )
            ->allowedFilters(
                AllowedFilter::exact('year'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'year',
                'is_active',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('-id')
            ->cursorPaginate($request->query('per_page', 10));

        return new CoreTeamCollection($coreTeams);
    }

    /**
     * Create a core team
     *
     * @apiResource App\Http\Resources\CoreTeam\CoreTeamResource status=201
     *
     * @apiResourceModel App\Models\CoreTeam
     */
    public function store(StoreCoreTeamRequest $request)
    {
        DB::transaction(function () use ($request, &$coreTeam) {
            $group = Group::create([
                'name' => 'Core Team '.$request->validated('year'),
                'guard_name' => 'api',
                'is_managed' => true,
            ]);

            $coreTeam = CoreTeam::create(
                array_merge($request->validated(), ['group_id' => $group->id])
            );

            if ($coreTeam->is_active) {
                CoreTeam::where('id', '!=', $coreTeam->id)
                    ->update(['is_active' => false]);
            }
        });

        return new CoreTeamResource($coreTeam);
    }

    /**
     * Retrieve a core team
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\CoreTeam\CoreTeamResource
     *
     * @apiResourceModel App\Models\CoreTeam
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `year`, `is_active`, `created_at`, `updated_at`. No-example
     */
    public function show(CoreTeam $coreTeam)
    {
        $coreTeam = QueryBuilder::for(CoreTeam::where('id', $coreTeam->id))
            ->allowedFields(
                'id',
                'year',
                'is_active',
                'created_at',
                'updated_at',
            )
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
        DB::transaction(function () use ($request, $coreTeam) {
            $coreTeam->update($request->validated());

            if ($request->has('year')) {
                $coreTeam->group->update(['name' => 'Core Team '.$coreTeam->year]);
                unset($coreTeam->group);
            }

            if ($coreTeam->is_active) {
                CoreTeam::where('id', '!=', $coreTeam->id)
                    ->update(['is_active' => false]);
            }
        });

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
        DB::transaction(function () use ($coreTeam) {
            $group = $coreTeam->group;
            $coreTeam->delete();
            $group->delete();
            unset($coreTeam->group);
        });

        return new CoreTeamResource($coreTeam);
    }
}
