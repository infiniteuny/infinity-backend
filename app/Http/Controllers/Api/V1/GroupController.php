<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\StoreGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Http\Resources\Group\GroupCollection;
use App\Http\Resources\Group\GroupResource;
use App\Jobs\CreateSsoGroup;
use App\Jobs\DeleteDocsGroup;
use App\Jobs\DeleteSsoGroup;
use App\Jobs\UpdateSsoGroup;
use App\Models\Group;
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Groups
 * Manage groups.
 */
class GroupController extends Controller
{
    use HasPagination;

    public function __construct()
    {
        $this->authorizeResource(Group::class, 'group');
    }

    /**
     * List all groups
     *
     * @apiResourceCollection App\Http\Resources\Group\GroupCollection
     *
     * @apiResourceModel App\Models\Group paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `guard_name`, `created_at`, `updated_at`. No-example
     * @queryParam filters[name] string Filter by partial `name`. No-example
     * @queryParam filters[guard_name] string Filter by exact `guard_name`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `name`, `guard_name`, `created_at`, `updated_at`. Default: `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $query = QueryBuilder::for(Group::class)
            ->allowedFields(
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at',
            )
            ->allowedFilters(
                'name',
                AllowedFilter::exact('guard_name'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at',
            )
            ->defaultSorts('-id');

        $groups = $this->cursorPaginateWithTieBreaker($query, $request);

        return new GroupCollection($groups);
    }

    /**
     * Create a group
     *
     * @apiResource App\Http\Resources\Group\GroupResource status=201
     *
     * @apiResourceModel App\Models\Group
     */
    public function store(StoreGroupRequest $request)
    {
        $group = Group::create($request->validated());

        CreateSsoGroup::dispatch($group);

        return new GroupResource($group);
    }

    /**
     * Retrieve a group
     *
     * @apiResource App\Http\Resources\Group\GroupResource
     *
     * @apiResourceModel App\Models\Group
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `guard_name`, `created_at`, `updated_at`. No-example
     */
    public function show(Group $group)
    {
        $group = QueryBuilder::for(Group::where('id', $group->id))
            ->allowedFields(
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at',
            )
            ->firstOrFail();

        return new GroupResource($group);
    }

    /**
     * Update a group
     *
     * @apiResource App\Http\Resources\Group\GroupResource
     *
     * @apiResourceModel App\Models\Group
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $group->update($request->validated());

        if ($group->sso_id) {
            UpdateSsoGroup::dispatch($group);
        } else {
            CreateSsoGroup::dispatch($group);
        }

        return new GroupResource($group);
    }

    /**
     * Delete a group
     *
     * @apiResource App\Http\Resources\Group\GroupResource
     *
     * @apiResourceModel App\Models\Group
     */
    public function destroy(Group $group)
    {
        $ssoId = $group->sso_id;
        $docsGroupId = $group->integration?->docs_group_id;

        Group::withoutTouching(fn () => $group->delete());

        if (is_string($ssoId) && $ssoId !== '') {
            DeleteSsoGroup::dispatch($ssoId, $group->id);
        }

        if (is_string($docsGroupId) && $docsGroupId !== '') {
            DeleteDocsGroup::dispatch($docsGroupId, $group->id);
        }

        return new GroupResource($group);
    }
}
