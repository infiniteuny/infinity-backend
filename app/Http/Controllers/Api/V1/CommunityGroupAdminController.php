<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommunityGroupAdmin\StoreCommunityGroupAdminRequest;
use App\Http\Requests\CommunityGroupAdmin\UpdateCommunityGroupAdminRequest;
use App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminCollection;
use App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource;
use App\Models\CommunityGroupAdmin;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Community Group Admin
 * Manage community group administrators.
 */
class CommunityGroupAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,'.CommunityGroupAdmin::class)->only('store');
        $this->middleware('can:update,community_group_admin')->only('update');
        $this->middleware('can:delete,community_group_admin')->only('destroy');
    }

    /**
     * List all community group administrators
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminCollection
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin paginate=10,cursor
     */
    public function index(Request $request)
    {
        $communityGroupAdmins = QueryBuilder::for(CommunityGroupAdmin::class)
            ->allowedFields([
                'id',
                'year',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->allowedFilters([
                AllowedFilter::exact('year'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'year',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->defaultSort([
                '-id',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new CommunityGroupAdminCollection($communityGroupAdmins);
    }

    /**
     * Create a community group administrator
     *
     * @apiResource App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource status=201
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin
     */
    public function store(StoreCommunityGroupAdminRequest $request)
    {
        DB::transaction(function () use ($request, &$communityGroupAdmin) {
            $group = Group::create([
                'name' => 'Community '.$request->safe()->only(['year'])['year'],
                'guard_name' => 'api',
                'is_managed' => true,
            ]);

            $communityGroupAdmin = CommunityGroupAdmin::create(
                array_merge($request->validated(), ['group_id' => $group->id])
            );

            if ($communityGroupAdmin->is_active) {
                CommunityGroupAdmin::where('id', '!=', $communityGroupAdmin->id)
                    ->update(['is_active' => false]);
            }
        });

        return new CommunityGroupAdminResource($communityGroupAdmin);
    }

    /**
     * Retrieve a community group administrator
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin
     */
    public function show(CommunityGroupAdmin $communityGroupAdmin)
    {
        $communityGroupAdmin = QueryBuilder::for(CommunityGroupAdmin::where('id', $communityGroupAdmin->id))
            ->allowedFields([
                'id',
                'year',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new CommunityGroupAdminResource($communityGroupAdmin);
    }

    /**
     * Update a community group administrator
     *
     * @apiResource App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin
     */
    public function update(UpdateCommunityGroupAdminRequest $request, CommunityGroupAdmin $communityGroupAdmin)
    {
        DB::transaction(function () use ($request, $communityGroupAdmin) {
            $communityGroupAdmin->update($request->validated());

            if ($request->has('year')) {
                $communityGroupAdmin->group->update(['name' => 'Community '.$communityGroupAdmin->year]);
                unset($communityGroupAdmin->group);
            }

            if ($communityGroupAdmin->is_active) {
                CommunityGroupAdmin::where('id', '!=', $communityGroupAdmin->id)
                    ->update(['is_active' => false]);
            }
        });

        return new CommunityGroupAdminResource($communityGroupAdmin);
    }

    /**
     * Delete a community group administrator
     *
     * @apiResource App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin
     */
    public function destroy(CommunityGroupAdmin $communityGroupAdmin)
    {
        DB::transaction(function () use ($communityGroupAdmin) {
            $communityGroupAdmin->group->delete();
            unset($communityGroupAdmin->group);
            $communityGroupAdmin->delete();
        });

        return new CommunityGroupAdminResource($communityGroupAdmin);
    }
}
