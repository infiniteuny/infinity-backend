<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\StoreGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Http\Resources\Group\GroupCollection;
use App\Http\Resources\Group\GroupResource;
use App\Jobs\CreateSsoGroup;
use App\Jobs\DeleteSsoGroup;
use App\Jobs\UpdateSsoGroup;
use App\Models\Group;
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
     */
    public function index(Request $request)
    {
        $groups = QueryBuilder::for(Group::class)
            ->allowedFields([
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at',
            ])
            ->allowedFilters([
                'name',
                AllowedFilter::exact('guard_name'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at',
            ])
            ->defaultSorts([
                '-id',
            ])
            ->cursorPaginate($request->query('per_page', 10));

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

        dispatch(new CreateSsoGroup($group));

        return new GroupResource($group);
    }

    /**
     * Retrieve a group
     *
     * @apiResource App\Http\Resources\Group\GroupResource
     *
     * @apiResourceModel App\Models\Group
     */
    public function show(Group $group)
    {
        $group = QueryBuilder::for(Group::where('id', $group->id))
            ->allowedFields([
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at',
            ])
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
            dispatch(new UpdateSsoGroup($group));
        } else {
            dispatch(new CreateSsoGroup($group));
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
        $group->delete();

        if ($group->sso_id) {
            dispatch(new DeleteSsoGroup($group->sso_id));
        }

        return new GroupResource($group);
    }
}
