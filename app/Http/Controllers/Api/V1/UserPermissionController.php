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
use Spatie\Permission\PermissionRegistrar;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group User Permissions
 * Manage user permissions.
 */
class UserPermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:viewAny,'.UserPermission::class.',user')->only('index');
        $this->middleware('can:view,user_permission')->only('show');
        $this->middleware('can:create,'.UserPermission::class.',user')->only('store');
        $this->middleware('can:update,user_permission')->only('update');
        $this->middleware('can:delete,user_permission')->only('destroy');
    }

    /**
     * List all user permissions
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\UserPermission\UserPermissionCollection
     *
     * @apiResourceModel App\Models\Permission states=pivotUserPermission paginate=10,cursor
     */
    public function index(User $user, Request $request)
    {
        $includes = explode(',', $request->query('includes', ''));

        if (in_array('nested', $includes)) {
            $userPermission = $user->getAllPermissions();
        } else {
            $userPermission = QueryBuilder::for($user->permissions())
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
        }

        return new UserPermissionCollection($userPermission);
    }

    /**
     * Create a user permission
     *
     * @apiResource App\Http\Resources\UserPermission\UserPermissionResource status=201
     *
     * @apiResourceModel App\Models\Permission states=pivotUserPermission
     */
    public function store(User $user, StoreUserPermissionRequest $request)
    {
        $user->permissions()->attach($request->validated('permission_id'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $userPermission = $user
            ->permissions()
            ->wherePivot('permission_id', $request->validated('permission_id'))
            ->first();

        return new UserPermissionResource($userPermission);
    }

    /**
     * Retrieve a user permission
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\UserPermission\UserPermissionResource
     *
     * @apiResourceModel App\Models\Permission states=pivotUserPermission
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
     *
     * @apiResource App\Http\Resources\UserPermission\UserPermissionResource
     *
     * @apiResourceModel App\Models\Permission states=pivotUserPermission
     */
    public function update(UpdateUserPermissionRequest $request, UserPermission $userPermission)
    {
        $userPermissionId = $userPermission->id;
        $user = $userPermission->user;

        $userPermission->update($request->validated());
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $userPermission = $user
            ->permissions()
            ->wherePivot('id', $userPermissionId)
            ->firstOrFail();

        return new UserPermissionResource($userPermission);
    }

    /**
     * Delete a user permission
     *
     * @apiResource App\Http\Resources\UserPermission\UserPermissionResource
     *
     * @apiResourceModel App\Models\Permission states=pivotUserPermission
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
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return new UserPermissionResource($userPermission);
    }
}
