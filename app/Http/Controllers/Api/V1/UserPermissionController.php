<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserPermission\StoreUserPermissionRequest;
use App\Http\Requests\UserPermission\UpdateUserPermissionRequest;
use App\Http\Resources\UserPermission\UserPermissionCollection;
use App\Http\Resources\UserPermission\UserPermissionResource;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermission;
use App\Traits\HasPagination;
use App\Traits\HasQueryFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\Includes\IncludeInterface;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group User Permissions
 * Manage user permissions.
 */
class UserPermissionController extends Controller
{
    use HasPagination, HasQueryFilters;

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
     * @apiResourceCollection App\Http\Resources\UserPermission\UserPermissionCollection
     *
     * @apiResourceModel App\Models\Permission states=pivotUserPermission paginate=10,cursor
     *
     * @queryParam includes string Comma-separated list of options to include. Use `nested` to return all direct and group-inherited permissions without pagination. No-example
     * @queryParam filters[name] string Filter by exact `name`. No-example
     * @queryParam filters[guard_name] string Filter by exact `guard_name`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `name`, `guard_name`, `created_at`, `updated_at`. Default: `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(User $user, Request $request)
    {
        $includes = $this->parseRequestedIncludes($request);
        $isNested = in_array('nested', $includes, true);

        $nestedInclude = AllowedInclude::custom('nested', new class implements IncludeInterface
        {
            public function __invoke(Builder $query, string $include): void
            {
                // Handled via subject query selection below
            }
        });

        $subject = $isNested
            ? Permission::query()->where(function (Builder $query) use ($user) {
                $query->whereHas('users', fn (Builder $userQuery) => $userQuery->where('users.id', $user->id))
                    ->orWhereHas('groups.users', fn (Builder $groupUserQuery) => $groupUserQuery->where('users.id', $user->id));
            })
            : $user->permissions();

        $query = QueryBuilder::for($subject, $request)
            ->allowedFields(
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at',
            )
            ->allowedIncludes($nestedInclude)
            ->allowedFilters(
                AllowedFilter::exact('name', 'permissions.name', false),
                AllowedFilter::exact('guard_name', 'permissions.guard_name', false),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC, 'and', 'permissions.created_at', false),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC, 'and', 'permissions.updated_at', false),
            )
            ->allowedSorts(
                AllowedSort::field('id', 'permissions.id'),
                AllowedSort::field('name', 'permissions.name'),
                AllowedSort::field('guard_name', 'permissions.guard_name'),
                AllowedSort::field('created_at', 'permissions.created_at'),
                AllowedSort::field('updated_at', 'permissions.updated_at'),
            )
            ->defaultSorts('-permissions.id');

        $userPermission = $isNested
            ? $query->get()
            : $this->cursorPaginateWithTieBreaker($query, $request);

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

        return (new UserPermissionResource($userPermission))->response()->setStatusCode(201);
    }

    /**
     * Retrieve a user permission
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
