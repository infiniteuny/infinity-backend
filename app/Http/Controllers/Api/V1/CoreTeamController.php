<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoreTeam\StoreCoreTeamRequest;
use App\Http\Requests\CoreTeam\UpdateCoreTeamRequest;
use App\Http\Resources\CoreTeam\CoreTeamCollection;
use App\Http\Resources\CoreTeam\CoreTeamResource;
use App\Jobs\DeleteBlob;
use App\Jobs\DeleteDocsGroup;
use App\Jobs\DeleteSsoGroup;
use App\Models\CoreTeam;
use App\Models\CoreTeamMember;
use App\Models\Group;
use App\Models\User;
use App\Traits\HasPagination;
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
    use HasPagination;

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
        $query = QueryBuilder::for(CoreTeam::class)
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
            ->defaultSorts('-id');

        $coreTeams = $this->cursorPaginateWithTieBreaker($query, $request);

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
        $data = $request->validated();

        DB::transaction(function () use ($data, &$coreTeam) {
            if ($data['is_active'] ?? false) {
                CoreTeam::where('is_active', true)->lockForUpdate()->get();
            }

            $group = Group::create([
                'name' => 'Core Team '.$data['year'],
                'guard_name' => 'api',
                'is_managed' => true,
            ]);

            $coreTeam = CoreTeam::create(
                array_merge($data, ['group_id' => $group->id])
            );

            if ($coreTeam->is_active) {
                CoreTeam::where('is_active', true)
                    ->where('id', '!=', $coreTeam->id)
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
        $data = $request->validated();

        DB::transaction(function () use ($request, $data, $coreTeam) {
            if ($data['is_active'] ?? false) {
                CoreTeam::where('is_active', true)->lockForUpdate()->get();
            }

            $coreTeam->update($data);

            if ($request->has('year')) {
                $coreTeam->group->update(['name' => 'Core Team '.$coreTeam->year]);
                unset($coreTeam->group);
            }

            if ($coreTeam->is_active) {
                CoreTeam::where('is_active', true)
                    ->where('id', '!=', $coreTeam->id)
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
        $ssoId = null;
        $groupId = null;
        $docsGroupId = null;
        $memberEmails = [];
        $blobManifests = [];
        $sharedGroupIds = array_values(array_filter([
            config('services.outline.core_team_group_id'),
            config('services.outline.xcore_team_group_id'),
        ]));

        DB::transaction(function () use (
            $coreTeam,
            $sharedGroupIds,
            &$ssoId,
            &$groupId,
            &$docsGroupId,
            &$memberEmails,
            &$blobManifests,
        ) {
            $group = $coreTeam->group()->with('integration:id,group_id,docs_group_id')->first();
            $ssoId = $group?->sso_id;
            $groupId = $group?->id;
            $docsGroupId = $group?->integration?->docs_group_id;

            if (is_string($docsGroupId) && $docsGroupId !== '' && $sharedGroupIds !== []) {
                $userGroupsTable = config('permission.table_names.model_has_roles', 'user_groups');
                $pivotRoleKey = config('permission.column_names.role_pivot_key') ?: 'group_id';
                $pivotUserKey = config('permission.column_names.model_morph_key', 'user_id');

                $memberEmails = User::query()
                    ->where(function ($query) use ($coreTeam, $groupId, $userGroupsTable, $pivotRoleKey, $pivotUserKey) {
                        $query->whereIn('id', CoreTeamMember::where('core_team_id', $coreTeam->id)->select('user_id'));

                        if ($groupId !== null) {
                            $query->orWhereIn(
                                'id',
                                DB::table($userGroupsTable)->where($pivotRoleKey, $groupId)->select($pivotUserKey),
                            );
                        }
                    })
                    ->pluck('email_address')
                    ->map(fn ($email) => is_string($email) ? strtolower(trim($email)) : '')
                    ->filter(fn (string $email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
                    ->unique()
                    ->values()
                    ->all();
            }

            $memberBlobs = DB::table('core_team_members')
                ->where('core_team_id', $coreTeam->id)
                ->get(['photo', 'animation']);

            foreach ($memberBlobs as $member) {
                foreach ([$member->photo, $member->animation] as $manifest) {
                    if (is_string($manifest) && $manifest !== '') {
                        $blobManifests[$manifest] = true;
                    }
                }
            }

            Group::withoutTouching(function () use ($coreTeam, $group) {
                $coreTeam->delete();
                $group?->delete();
            });
            unset($coreTeam->group);
        });

        if (is_string($ssoId) && $ssoId !== '') {
            DeleteSsoGroup::dispatch($ssoId, $groupId);
        }

        if (is_string($docsGroupId) && $docsGroupId !== '') {
            DeleteDocsGroup::dispatch($docsGroupId, $groupId, $sharedGroupIds, $memberEmails);
        }

        foreach (array_keys($blobManifests) as $manifest) {
            DeleteBlob::dispatch($manifest);
        }

        return new CoreTeamResource($coreTeam);
    }
}
