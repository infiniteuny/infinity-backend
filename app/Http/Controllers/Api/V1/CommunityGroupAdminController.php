<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommunityGroupAdmin\StoreCommunityGroupAdminRequest;
use App\Http\Requests\CommunityGroupAdmin\UpdateCommunityGroupAdminRequest;
use App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminCollection;
use App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource;
use App\Models\CommunityGroupAdmin;
use Illuminate\Http\Request;
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
        $this->authorizeResource(CommunityGroupAdmin::class, 'community_group_admin');
    }

    /**
     * List all community group administrators.
     *
     * @apiResourceCollection App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminCollection
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin
     */
    public function index(Request $request)
    {
        $communityGroupAdmins = QueryBuilder::for(CommunityGroupAdmin::class)
            ->allowedFields([
                'id',
                'year',
                'created_at',
                'updated_at',
            ])
            ->allowedFilters([
                AllowedFilter::exact('year'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'year',
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
     * Create a community group administrator.
     *
     * @apiResource App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin
     */
    public function store(StoreCommunityGroupAdminRequest $request)
    {
        $communityGroupAdmin = CommunityGroupAdmin::create($request->validated());

        return new CommunityGroupAdminResource($communityGroupAdmin);
    }

    /**
     * Retrieve a community group administrator.
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
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new CommunityGroupAdminResource($communityGroupAdmin);
    }

    /**
     * Update a community group administrator.
     *
     * @apiResource App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin
     */
    public function update(UpdateCommunityGroupAdminRequest $request, CommunityGroupAdmin $communityGroupAdmin)
    {
        $communityGroupAdmin->update($request->validated());

        return new CommunityGroupAdminResource($communityGroupAdmin);
    }

    /**
     * Delete a community group administrator.
     *
     * @apiResource App\Http\Resources\CommunityGroupAdmin\CommunityGroupAdminResource
     *
     * @apiResourceModel App\Models\CommunityGroupAdmin
     */
    public function destroy(CommunityGroupAdmin $communityGroupAdmin)
    {
        $communityGroupAdmin->delete();

        return new CommunityGroupAdminResource($communityGroupAdmin);
    }
}
