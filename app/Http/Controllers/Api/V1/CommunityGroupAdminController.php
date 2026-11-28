<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommunityGroupAdmin\StoreCommunityGroupAdminRequest;
use App\Http\Requests\CommunityGroupAdmin\UpdateCommunityGroupAdminRequest;
use App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminCollection;
use App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource;
use App\Jobs\DeleteBlob;
use App\Jobs\DeleteDocsGroup;
use App\Jobs\DeleteSsoGroup;
use App\Models\CommunityGroupAdmin;
use App\Models\Group;
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Community Group Admins
 * Manage community group administrators.
 */
class CommunityGroupAdminController extends Controller
{
    use HasPagination;

    public function __construct()
    {
        $this->middleware('can:create,'.CommunityGroupAdmin::class)->only('store');
        $this->middleware('can:update,community_group_admin')->only('update');
        $this->middleware('can:delete,community_group_admin')->only('destroy');
    }

    /**
     * List all community group administrators
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminCollection
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `year`, `is_active`, `created_at`, `updated_at`. No-example
     * @queryParam filters[year] integer Filter by exact `year`. No-example
     * @queryParam filters[is_active] boolean Filter by exact `is_active`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `year`, `is_active`, `created_at`, `updated_at`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $query = QueryBuilder::for(CommunityGroupAdmin::class)
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
            ->defaultSort('-id');

        $communityGroupAdmins = $this->cursorPaginateWithTieBreaker($query, $request);

        return new CommunityGroupAdminCollection($communityGroupAdmins);
    }

    /**
     * Create a community group administrator
     *
     * @apiResource App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource status=201
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin
     */
    public function store(StoreCommunityGroupAdminRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, &$communityGroupAdmin) {
            if ($data['is_active'] ?? false) {
                CommunityGroupAdmin::where('is_active', true)->lockForUpdate()->get();
            }

            $group = Group::create([
                'name' => 'CG Admin '.$data['year'],
                'guard_name' => 'api',
                'is_managed' => true,
            ]);

            $communityGroupAdmin = CommunityGroupAdmin::create(
                array_merge($data, ['group_id' => $group->id])
            );

            if ($communityGroupAdmin->is_active) {
                CommunityGroupAdmin::where('is_active', true)
                    ->where('id', '!=', $communityGroupAdmin->id)
                    ->update(['is_active' => false]);
            }
        });

        return new CommunityGroupAdminResource($communityGroupAdmin);
    }

    /**
     * Retrieve a community group administrator
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `year`, `is_active`, `created_at`, `updated_at`. No-example
     */
    public function show(CommunityGroupAdmin $communityGroupAdmin)
    {
        $communityGroupAdmin = QueryBuilder::for(CommunityGroupAdmin::where('id', $communityGroupAdmin->id))
            ->allowedFields(
                'id',
                'year',
                'is_active',
                'created_at',
                'updated_at',
            )
            ->firstOrFail();

        return new CommunityGroupAdminResource($communityGroupAdmin);
    }

    /**
     * Update a community group administrator
     *
     * @apiResource App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin
     */
    public function update(UpdateCommunityGroupAdminRequest $request, CommunityGroupAdmin $communityGroupAdmin)
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data, $communityGroupAdmin) {
            if ($data['is_active'] ?? false) {
                CommunityGroupAdmin::where('is_active', true)->lockForUpdate()->get();
            }

            $communityGroupAdmin->update($data);

            if ($request->has('year')) {
                $communityGroupAdmin->group->update(['name' => 'CG Admin '.$communityGroupAdmin->year]);
                unset($communityGroupAdmin->group);
            }

            if ($communityGroupAdmin->is_active) {
                CommunityGroupAdmin::where('is_active', true)
                    ->where('id', '!=', $communityGroupAdmin->id)
                    ->update(['is_active' => false]);
            }
        });

        return new CommunityGroupAdminResource($communityGroupAdmin);
    }

    /**
     * Delete a community group administrator
     *
     * @apiResource App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin
     */
    public function destroy(CommunityGroupAdmin $communityGroupAdmin)
    {
        $ssoId = null;
        $groupId = null;
        $docsGroupId = null;
        $blobManifests = [];

        DB::transaction(function () use (
            $communityGroupAdmin,
            &$ssoId,
            &$groupId,
            &$docsGroupId,
            &$blobManifests,
        ) {
            $group = $communityGroupAdmin->group()->with('integration:id,group_id,docs_group_id')->first();
            $ssoId = $group?->sso_id;
            $groupId = $group?->id;
            $docsGroupId = $group?->integration?->docs_group_id;

            $memberBlobs = DB::table('community_group_admin_members')
                ->where('community_group_admin_id', $communityGroupAdmin->id)
                ->get(['photo', 'animation']);

            foreach ($memberBlobs as $member) {
                foreach ([$member->photo, $member->animation] as $manifest) {
                    if (is_string($manifest) && $manifest !== '') {
                        $blobManifests[$manifest] = true;
                    }
                }
            }

            Group::withoutTouching(function () use ($communityGroupAdmin, $group) {
                $communityGroupAdmin->delete();
                $group?->delete();
            });
            unset($communityGroupAdmin->group);
        });

        if (is_string($ssoId) && $ssoId !== '') {
            DeleteSsoGroup::dispatch($ssoId);
        }

        if (is_string($docsGroupId) && $docsGroupId !== '') {
            DeleteDocsGroup::dispatch($docsGroupId, $groupId);
        }

        foreach (array_keys($blobManifests) as $manifest) {
            DeleteBlob::dispatch($manifest);
        }

        return new CommunityGroupAdminResource($communityGroupAdmin);
    }
}
