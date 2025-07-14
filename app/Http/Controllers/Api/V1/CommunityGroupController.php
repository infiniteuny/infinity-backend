<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StorageVisibility;
use App\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommunityGroup\StoreCommunityGroupRequest;
use App\Http\Requests\CommunityGroup\UpdateCommunityGroupRequest;
use App\Http\Resources\CommunityGroup\CommunityGroupCollection;
use App\Http\Resources\CommunityGroup\CommunityGroupResource;
use App\Jobs\DeleteBlob;
use App\Models\CommunityGroup;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Community Groups
 * Manage community groups.
 */
class CommunityGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,'.CommunityGroup::class)->only('store');
        $this->middleware('can:update,community_group')->only('update');
        $this->middleware('can:delete,community_group')->only('destroy');
    }

    /**
     * List all community groups
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\CommunityGroup\CommunityGroupCollection
     *
     * @apiResourceModel App\Models\CommunityGroup paginate=10,cursor
     */
    public function index(Request $request)
    {
        $communityGroups = QueryBuilder::for(CommunityGroup::class)
            ->allowedFields([
                'id',
                'name',
                'priority',
                'description',
                'logo',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->allowedFilters([
                'name',
                AllowedFilter::exact('priority'),
                'description',
                AllowedFilter::exact('is_active'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'name',
                'priority',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->defaultSort([
                'priority',
                '-id',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new CommunityGroupCollection($communityGroups);
    }

    /**
     * Create a community group
     *
     * @apiResource App\Http\Resources\CommunityGroup\CommunityGroupResource status=201
     *
     * @apiResourceModel App\Models\CommunityGroup
     */
    public function store(StoreCommunityGroupRequest $request)
    {
        $manifest = Storage::store(
            $request->file('logo'),
            'community-groups/logos',
            StorageVisibility::PUBLIC,
        );

        $communityGroup = CommunityGroup::create(
            array_replace($request->validated(), ['logo' => $manifest])
        );

        return new CommunityGroupResource($communityGroup);
    }

    /**
     * Retrieve a community group
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\CommunityGroup\CommunityGroupResource
     *
     * @apiResourceModel App\Models\CommunityGroup
     */
    public function show(CommunityGroup $communityGroup)
    {
        $communityGroup = QueryBuilder::for(CommunityGroup::where('id', $communityGroup->id))
            ->allowedFields([
                'id',
                'name',
                'priority',
                'description',
                'logo',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new CommunityGroupResource($communityGroup);
    }

    /**
     * Update a community group
     *
     * @apiResource App\Http\Resources\CommunityGroup\CommunityGroupResource
     *
     * @apiResourceModel App\Models\CommunityGroup
     */
    public function update(UpdateCommunityGroupRequest $request, CommunityGroup $communityGroup)
    {
        $hasLogo = $request->has('logo');

        if ($hasLogo) {
            $oldManifest = $communityGroup->getRawOriginal('logo');

            DeleteBlob::dispatch($oldManifest);

            $manifest = Storage::store(
                $request->file('logo'),
                'community-groups/logos',
                StorageVisibility::PUBLIC,
            );
        }

        $communityGroup->update(
            $hasLogo
                ? array_replace($request->validated(), ['logo' => $manifest])
                : $request->validated()
        );

        return new CommunityGroupResource($communityGroup);
    }

    /**
     * Delete a community group
     *
     * @apiResource App\Http\Resources\CommunityGroup\CommunityGroupResource
     *
     * @apiResourceModel App\Models\CommunityGroup
     */
    public function destroy(CommunityGroup $communityGroup)
    {
        $manifest = $communityGroup->getRawOriginal('logo');

        DeleteBlob::dispatch($manifest);

        $communityGroup->delete();

        return new CommunityGroupResource($communityGroup);
    }
}
