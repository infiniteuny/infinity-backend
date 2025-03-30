<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserPermission\StoreUserPermissionRequest;
use App\Http\Requests\UserPermission\UpdateUserPermissionRequest;
use App\Http\Resources\UserPermission\UserPermissionCollection;
use App\Http\Resources\UserPermission\UserPermissionResource;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group User Permission
 * Manage user permissions.
 */
class UserPermissionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(UserPermission::class, 'user_permission');
    }

    /**
     * List all user permissions
     */
    public function index(User $user, Request $request)
    {
        $userPermission = QueryBuilder::for($user->groups())
            ->cursorPaginate($request->query('per_page', 10));

        return new UserPermissionCollection($userPermission);
    }

    /**
     * Create a user permission
     */
    public function store(User $user, StoreUserPermissionRequest $request)
    {
        $user->permissions()->attach($request->safe()->only('permission_id'));

        $userPermission = $user
            ->permissions()
            ->wherePivot('permission_id', $request->safe()->only('permission_id'))
            ->first();

        return new UserPermissionResource($userPermission);
    }

    /**
     * Retrieve a user permission
     */
    public function show(UserPermission $userPermission)
    {
        $userPermissionId = $userPermission->id;
        $userPermission = $userPermission
            ->user
            ->permissions()
            ->wherePivot('id', $userPermissionId);

        $userPermission = QueryBuilder::for($userPermission)
            ->firstOrFail();

        return new UserPermissionResource($userPermission);
    }

    /**
     * Update a user permission
     */
    public function update(UpdateUserPermissionRequest $request, UserPermission $userPermission)
    {
        $userPermissionId = $userPermission->id;
        $user = $userPermission->user;

        $userPermission->update($request->validated());
        $userPermission = $user
            ->permissions()
            ->wherePivot('id', $userPermissionId)
            ->firstOrFail();

        return new UserPermissionResource($userPermission);
    }

    /**
     * Delete a user permission
     */
    public function destroy(UserPermission $userPermission)
    {
        $userPermissionId = $userPermission->id;
        $user = $userPermission->user;
        $userPermission = $user
            ->permissions()
            ->wherePivot('id', $userPermissionId)
            ->firstOrFail();

        $user->permissions()->detach($userPermission->id);

        return new UserPermissionResource($userPermission);
    }
}
