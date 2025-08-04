<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserGroup\StoreUserGroupRequest;
use App\Http\Requests\UserGroup\UpdateUserGroupRequest;
use App\Http\Resources\UserGroup\UserGroupCollection;
use App\Http\Resources\UserGroup\UserGroupResource;
use App\Models\User;
use App\Models\UserGroup;
use Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group User Groups
 * Manage user groups.
 */
class UserGroupController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(UserGroup::class, 'user_group');
    }

    /**
     * List all user groups
     *
     * @apiResourceCollection App\Http\Resources\UserGroup\UserGroupCollection
     *
     * @apiResourceModel App\Models\Group states=pivotUserGroup paginate=10,cursor
     */
    public function index(User $user, Request $request)
    {
        $userGroup = QueryBuilder::for($user->groups())
            ->cursorPaginate($request->query('per_page', 10));

        return new UserGroupCollection($userGroup);
    }

    /**
     * Create a user group
     *
     * @apiResource App\Http\Resources\UserGroup\UserGroupResource status=201
     *
     * @apiResourceModel App\Models\Group states=pivotUserGroup
     */
    public function store(User $user, StoreUserGroupRequest $request)
    {
        $user->groups()->attach($request->safe()->only('group_id'));

        $userGroup = $user
            ->groups()
            ->wherePivot('group_id', $request->safe()->only('group_id'))
            ->first();

        return new UserGroupResource($userGroup);
    }

    /**
     * Retrieve a user group
     *
     * @apiResource App\Http\Resources\UserGroup\UserGroupResource
     *
     * @apiResourceModel App\Models\Group states=pivotUserGroup
     */
    public function show(UserGroup $userGroup)
    {
        $userGroupId = $userGroup->id;
        $userGroup = $userGroup
            ->user
            ->groups()
            ->wherePivot('id', $userGroupId);

        $userGroup = QueryBuilder::for($userGroup)
            ->firstOrFail();

        return new UserGroupResource($userGroup);
    }

    /**
     * Update a user group
     *
     * @apiResource App\Http\Resources\UserGroup\UserGroupResource
     *
     * @apiResourceModel App\Models\Group states=pivotUserGroup
     */
    public function update(UpdateUserGroupRequest $request, UserGroup $userGroup)
    {
        $userGroupId = $userGroup->id;
        $user = $userGroup->user;

        $userGroup->update($request->validated());
        $userGroup = $user
            ->groups()
            ->wherePivot('id', $userGroupId)
            ->firstOrFail();

        return new UserGroupResource($userGroup);
    }

    /**
     * Delete a user group
     *
     * @apiResource App\Http\Resources\UserGroup\UserGroupResource
     *
     * @apiResourceModel App\Models\Group states=pivotUserGroup
     */
    public function destroy(UserGroup $userGroup)
    {
        $userGroupId = $userGroup->id;
        $user = $userGroup->user;
        $userGroup = $user
            ->groups()
            ->wherePivot('id', $userGroupId)
            ->firstOrFail();

        $user->groups()->detach($userGroup->id);

        return new UserGroupResource($userGroup);
    }
}
