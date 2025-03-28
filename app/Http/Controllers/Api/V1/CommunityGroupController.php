<?php

namespace App\Http\Controllers\Api\V1;

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
        $this->authorizeResource(CommunityGroup::class, 'community_group');
    }

    /**
     * List all community groups
     *
     * @apiResourceCollection App\Http\Resources\CommunityGroup\CommunityGroupCollection
     *
     * @apiResourceModel App\Models\CommunityGroup
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
                'created_at',
                'updated_at',
            ])
            ->allowedFilters([
                'name',
                AllowedFilter::exact('priority'),
                'description',
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'name',
                'priority',
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
     * @apiResource App\Http\Resources\CommunityGroup\CommunityGroupResource
     *
     * @apiResourceModel App\Models\CommunityGroup
     */
    public function store(StoreCommunityGroupRequest $request)
    {
        $manifest = Storage::store($request->file('logo'), 'images/community-groups');

        $communityGroup = CommunityGroup::create(
            array_replace($request->validated(), ['logo' => $manifest])
        );

        return new CommunityGroupResource($communityGroup);
    }

    /**
     * Retrieve a community group
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
            $encodedManifest = $communityGroup->getRawOriginal('logo');

            dispatch(new DeleteBlob($encodedManifest));

            $manifest = Storage::store($request->file('logo'), 'images/community-groups');
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
        $encodedManifest = $communityGroup->getRawOriginal('logo');

        dispatch(new DeleteBlob($encodedManifest));

        $communityGroup->delete();

        return new CommunityGroupResource($communityGroup);
    }
}
