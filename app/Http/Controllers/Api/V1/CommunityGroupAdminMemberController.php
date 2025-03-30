<?php

namespace App\Http\Controllers\Api\V1;

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
     */
    public function index(CommunityGroupAdmin $communityGroupAdmin, Request $request)
    {
        $communityGroupAdminMembers = QueryBuilder::for($communityGroupAdmin->members())
            ->cursorPaginate($request->query('per_page', 10));

        return new CommunityGroupAdminMemberCollection($communityGroupAdminMembers);
    }

    /**
     * Create a community group administrator member
     */
    public function store(CommunityGroupAdmin $communityGroupAdmin, StoreCommunityGroupAdminMemberRequest $request)
    {
        $photoManifest = Storage::store($request->file('photo'), 'images/community-group-admins/photos');

        $hasAnimation = $request->has('animation');

        if ($hasAnimation) {
            $animationManifest = Storage::store($request->file('animation'), 'images/community-group-admins/animations');
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
     */
    public function update(UpdateCommunityGroupAdminMemberRequest $request, CommunityGroupAdminMember $communityGroupAdminMember)
    {
        $communityGroupAdminMemberId = $communityGroupAdminMember->id;
        $communityGroupAdmin = $communityGroupAdminMember->communityGroupAdmin;

        $hasPhoto = $request->has('image');
        $hasAnimation = $request->has('animation');
        $hasFileAnimation = $request->hasFile('animation');

        if ($hasPhoto) {
            $photoEncodedManifest = $communityGroupAdminMember->getRawOriginal('photo');

            dispatch(new DeleteBlob($photoEncodedManifest));

            $photoManifest = Storage::store($request->file('photo'), 'images/community-group-admins/photos');
        }

        if ($hasAnimation) {
            $animationEncodedManifest = $communityGroupAdminMember->getRawOriginal('animation');

            dispatch(new DeleteBlob($animationEncodedManifest));

            if ($hasFileAnimation) {
                $animationManifest = Storage::store($request->file('animation'), 'images/community-group-admins/animations');
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
     */
    public function destroy(CommunityGroupAdminMember $communityGroupAdminMember)
    {
        $communityGroupAdminMemberId = $communityGroupAdminMember->id;
        $communityGroupAdmin = $communityGroupAdminMember->communityGroupAdmin;
        $communityGroupAdminMember = $communityGroupAdmin
            ->members()
            ->wherePivot('id', $communityGroupAdminMemberId)
            ->firstOrFail();

        $photoEncodedManifest = $communityGroupAdminMember->getRawOriginal('photo');
        $animationEncodedManifest = $communityGroupAdminMember->getRawOriginal('animation');

        dispatch(new DeleteBlob($photoEncodedManifest));
        dispatch(new DeleteBlob($animationEncodedManifest));

        $communityGroupAdmin->members()->detach($communityGroupAdminMember->id);

        return new CommunityGroupAdminMemberResource($communityGroupAdminMember);
    }
}
