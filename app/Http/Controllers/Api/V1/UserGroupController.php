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
 * @group User Group
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
     */
    public function index(User $user, Request $request)
    {
        $userGroup = QueryBuilder::for($user->groups())
            ->cursorPaginate($request->query('per_page', 10));

        return new UserGroupCollection($userGroup);
    }

    /**
     * Create a user group
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
