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
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Group Permission
 * Manage group permissions.
 */
class GroupPermissionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(GroupPermission::class, 'group_permission');
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
        $groupPermission = QueryBuilder::for($group->groups())
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
