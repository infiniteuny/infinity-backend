<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Http\Resources\Permission\PermissionCollection;
use App\Http\Resources\Permission\PermissionResource;
use App\Models\Permission;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Permissions
 * Manage permissions.
 */
class PermissionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Permission::class, 'permission');
    }

    /**
     * List all permissions
     *
     * @apiResourceCollection App\Http\Resources\Permission\PermissionCollection
     *
     * @apiResourceModel App\Models\Permission paginate=10,cursor
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
        $permissions = QueryBuilder::for(Permission::class)
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
            ->defaultSorts('-id')
            ->cursorPaginate($request->query('per_page', 10));

        return new PermissionCollection($permissions);
    }

    /**
     * Create a permission
     *
     * @apiResource App\Http\Resources\Permission\PermissionResource status=201
     *
     * @apiResourceModel App\Models\Permission
     */
    public function store(StorePermissionRequest $request)
    {
        $permission = Permission::create($request->validated());

        return new PermissionResource($permission);
    }

    /**
     * Retrieve a permission
     *
     * @apiResource App\Http\Resources\Permission\PermissionResource
     *
     * @apiResourceModel App\Models\Permission
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `guard_name`, `created_at`, `updated_at`. No-example
     */
    public function show(Permission $permission)
    {
        $permission = QueryBuilder::for(Permission::where('id', $permission->id))
            ->allowedFields(
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at',
            )
            ->firstOrFail();

        return new PermissionResource($permission);
    }

    /**
     * Update a permission
     *
     * @apiResource App\Http\Resources\Permission\PermissionResource
     *
     * @apiResourceModel App\Models\Permission
     */
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $permission->update($request->validated());

        return new PermissionResource($permission);
    }

    /**
     * Delete a permission
     *
     * @apiResource App\Http\Resources\Permission\PermissionResource
     *
     * @apiResourceModel App\Models\Permission
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return new PermissionResource($permission);
    }
}
