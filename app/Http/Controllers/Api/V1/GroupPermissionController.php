<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupPermission\StoreGroupPermissionRequest;
use App\Http\Requests\GroupPermission\UpdateGroupPermissionRequest;
use App\Http\Resources\GroupPermission\GroupPermissionCollection;
use App\Http\Resources\GroupPermission\GroupPermissionResource;
use App\Models\Group;
use App\Models\GroupPermission;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Group Permissions
 * Manage group permissions.
 */
class GroupPermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:viewAny,'.GroupPermission::class)->only('index');
        $this->middleware('can:view,group_permission')->only('show');
        $this->middleware('can:create,'.GroupPermission::class)->only('store');
        $this->middleware('can:update,group_permission')->only('update');
        $this->middleware('can:delete,group_permission')->only('destroy');
    }

    /**
     * List all group permissions
     *
     * @apiResourceCollection App\Http\Resources\GroupPermission\GroupPermissionCollection
     *
     * @apiResourceModel App\Models\Permission states=pivotGroupPermission paginate=10,cursor
     */
    public function index(Group $group, Request $request)
    {
        $groupPermission = QueryBuilder::for($group->permissions())
            ->allowedFilters(
                AllowedFilter::exact('name', 'permissions.name'),
                AllowedFilter::exact('guard_name', 'permissions.guard_name'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC, 'and', 'permissions.created_at'),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC, 'and', 'permissions.updated_at'),
            )
            ->allowedSorts(
                AllowedSort::field('id', 'permissions.id'),
                AllowedSort::field('name', 'permissions.name'),
                AllowedSort::field('guard_name', 'permissions.guard_name'),
                AllowedSort::field('created_at', 'permissions.created_at'),
                AllowedSort::field('updated_at', 'permissions.updated_at'),
            )
            ->defaultSorts('-permissions.id')
            ->cursorPaginate($request->query('per_page', 10));

        return new GroupPermissionCollection($groupPermission);
    }

    /**
     * Create a group permission
     *
     * @apiResource App\Http\Resources\GroupPermission\GroupPermissionResource status=201
     *
     * @apiResourceModel App\Models\Permission states=pivotGroupPermission
     */
    public function store(Group $group, StoreGroupPermissionRequest $request)
    {
        $group->permissions()->attach($request->safe()->only('permission_id'));

        $groupPermission = $group
            ->permissions()
            ->wherePivot('permission_id', $request->safe()->only('permission_id'))
            ->first();

        return new GroupPermissionResource($groupPermission);
    }

    /**
     * Retrieve a group permission
     *
     * @apiResource App\Http\Resources\GroupPermission\GroupPermissionResource
     *
     * @apiResourceModel App\Models\Permission states=pivotGroupPermission
     */
    public function show(GroupPermission $groupPermission)
    {
        $groupPermissionId = $groupPermission->id;
        $groupPermission = $groupPermission
            ->group
            ->permissions()
            ->wherePivot('id', $groupPermissionId);

        $groupPermission = QueryBuilder::for($groupPermission)
            ->firstOrFail();

        return new GroupPermissionResource($groupPermission);
    }

    /**
     * Update a group permission
     *
     * @apiResource App\Http\Resources\GroupPermission\GroupPermissionResource
     *
     * @apiResourceModel App\Models\Permission states=pivotGroupPermission
     */
    public function update(UpdateGroupPermissionRequest $request, GroupPermission $groupPermission)
    {
        $groupPermissionId = $groupPermission->id;
        $group = $groupPermission->group;

        $groupPermission->update($request->validated());
        $groupPermission = $group
            ->permissions()
            ->wherePivot('id', $groupPermissionId)
            ->firstOrFail();

        return new GroupPermissionResource($groupPermission);
    }

    /**
     * Delete a group permission
     *
     * @apiResource App\Http\Resources\GroupPermission\GroupPermissionResource
     *
     * @apiResourceModel App\Models\Permission states=pivotGroupPermission
     */
    public function destroy(GroupPermission $groupPermission)
    {
        $groupPermissionId = $groupPermission->id;
        $group = $groupPermission->group;
        $groupPermission = $group
            ->permissions()
            ->wherePivot('id', $groupPermissionId)
            ->firstOrFail();

        $group->permissions()->detach($groupPermission->id);

        return new GroupPermissionResource($groupPermission);
    }
}
