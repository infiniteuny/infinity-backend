<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StorageVisibility;
use App\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommunityGroupAdminMember\StoreCommunityGroupAdminMemberRequest;
use App\Http\Requests\CommunityGroupAdminMember\UpdateCommunityGroupAdminMemberRequest;
use App\Http\Resources\CommunityGroupAdminMember\CommunityGroupAdminMemberCollection;
use App\Http\Resources\CommunityGroupAdminMember\CommunityGroupAdminMemberResource;
use App\Jobs\DeleteBlob;
use App\Models\CommunityGroupAdmin;
use App\Models\CommunityGroupAdminMember;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Community Group Admin Member
 * Manage community group administrator members.
 */
class CommunityGroupAdminMemberController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CommunityGroupAdminMember::class, 'community_group_admin_member');
    }

    /**
     * List all community group administrator members
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\CommunityGroupAdminMember\CommunityGroupAdminMemberCollection
     *
     * @apiResourceModel App\Models\User states=pivotCommunityGroupAdminMember paginate=10,cursor
     */
    public function index(CommunityGroupAdmin $communityGroupAdmin, Request $request)
    {
        $communityGroupAdminMembers = QueryBuilder::for($communityGroupAdmin->members())
            ->cursorPaginate($request->query('per_page', 10));

        return new CommunityGroupAdminMemberCollection($communityGroupAdminMembers);
    }

    /**
     * Create a community group administrator member
     *
     * @apiResource App\Http\Resources\CommunityGroupAdminMember\CommunityGroupAdminMemberResource status=201
     *
     * @apiResourceModel App\Models\User states=pivotCommunityGroupAdminMember
     */
    public function store(CommunityGroupAdmin $communityGroupAdmin, StoreCommunityGroupAdminMemberRequest $request)
    {
        $photoManifest = Storage::store(
            $request->file('photo'),
            'community-group-admins/photos',
            StorageVisibility::PUBLIC,
        );

        $hasAnimation = $request->has('animation');

        if ($hasAnimation) {
            $animationManifest = Storage::store(
                $request->file('animation'),
                'community-group-admins/animations',
                StorageVisibility::PUBLIC,
            );
        }

        $communityGroupAdmin->members()->attach($request->safe()->only('user_id'), [
            'community_group_id' => $request->safe()->only('community_group_id'),
            'photo' => $photoManifest,
            'animation' => $hasAnimation ? $animationManifest : null,
        ]);

        $communityGroupAdminMember = $communityGroupAdmin
            ->members()
            ->wherePivot('user_id', $request->safe()->only('user_id'))
            ->first();

        return new CommunityGroupAdminMemberResource($communityGroupAdminMember);
    }

    /**
     * Retrieve a community group administrator member
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\CommunityGroupAdminMember\CommunityGroupAdminMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotCommunityGroupAdminMember
     */
    public function show(CommunityGroupAdminMember $communityGroupAdminMember)
    {
        $communityGroupAdminMemberId = $communityGroupAdminMember->id;
        $communityGroupAdminMember = $communityGroupAdminMember
            ->communityGroupAdmin
            ->members()
            ->wherePivot('id', $communityGroupAdminMemberId);

        $communityGroupAdminMember = QueryBuilder::for($communityGroupAdminMember)
            ->firstOrFail();

        return new CommunityGroupAdminMemberResource($communityGroupAdminMember);
    }

    /**
     * Update a community group administrator member
     *
     * @apiResource App\Http\Resources\CommunityGroupAdminMember\CommunityGroupAdminMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotCommunityGroupAdminMember
     */
    public function update(UpdateCommunityGroupAdminMemberRequest $request, CommunityGroupAdminMember $communityGroupAdminMember)
    {
        $communityGroupAdminMemberId = $communityGroupAdminMember->id;
        $communityGroupAdmin = $communityGroupAdminMember->communityGroupAdmin;

        $hasPhoto = $request->has('image');
        $hasAnimation = $request->has('animation');
        $hasFileAnimation = $request->hasFile('animation');

        if ($hasPhoto) {
            $oldPhotoManifest = $communityGroupAdminMember->getRawOriginal('photo');

            dispatch(new DeleteBlob($oldPhotoManifest));

            $photoManifest = Storage::store(
                $request->file('photo'),
                'community-group-admins/photos',
                StorageVisibility::PUBLIC,
            );
        }

        if ($hasAnimation) {
            $oldAnimationManifest = $communityGroupAdminMember->getRawOriginal('animation');

            dispatch(new DeleteBlob($oldAnimationManifest));

            if ($hasFileAnimation) {
                $animationManifest = Storage::store(
                    $request->file('animation'),
                    'community-group-admins/animations',
                    StorageVisibility::PUBLIC,
                );
            } else {
                $animationManifest = null;
            }
        }

        $communityGroupAdminMember->update(
            $hasPhoto || $hasAnimation
                ? array_replace($request->validated(), [
                    ...($hasPhoto ? ['photo' => $photoManifest] : []),
                    ...($hasAnimation ? ['animation' => $animationManifest] : []),
                ])
                : $request->validated()
        );
        $communityGroupAdminMember = $communityGroupAdmin
            ->members()
            ->wherePivot('id', $communityGroupAdminMemberId)
            ->firstOrFail();

        return new CommunityGroupAdminMemberResource($communityGroupAdminMember);
    }

    /**
     * Delete a community group administrator member
     *
     * @apiResource App\Http\Resources\CommunityGroupAdminMember\CommunityGroupAdminMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotCommunityGroupAdminMember
     */
    public function destroy(CommunityGroupAdminMember $communityGroupAdminMember)
    {
        $communityGroupAdminMemberId = $communityGroupAdminMember->id;
        $communityGroupAdmin = $communityGroupAdminMember->communityGroupAdmin;
        $communityGroupAdminMember = $communityGroupAdmin
            ->members()
            ->wherePivot('id', $communityGroupAdminMemberId)
            ->firstOrFail();

        $photoManifest = $communityGroupAdminMember->getRawOriginal('photo');
        $animationManifest = $communityGroupAdminMember->getRawOriginal('animation');

        dispatch(new DeleteBlob($photoManifest));
        dispatch(new DeleteBlob($animationManifest));

        $communityGroupAdmin->members()->detach($communityGroupAdminMember->id);

        return new CommunityGroupAdminMemberResource($communityGroupAdminMember);
    }
}
