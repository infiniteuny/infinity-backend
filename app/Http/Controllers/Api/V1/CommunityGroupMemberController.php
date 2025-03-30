<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommunityGroupMember\StoreCommunityGroupMemberRequest;
use App\Http\Requests\CommunityGroupMember\UpdateCommunityGroupMemberRequest;
use App\Http\Resources\CommunityGroupMember\CommunityGroupMemberCollection;
use App\Http\Resources\CommunityGroupMember\CommunityGroupMemberResource;
use App\Models\CommunityGroup;
use App\Models\CommunityGroupMember;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Community Group Members
 * Manage community group members.
 */
class CommunityGroupMemberController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CommunityGroupMember::class, 'community_group_member');
    }

    /**
     * List all community group members
     */
    public function index(CommunityGroup $communityGroup, Request $request)
    {
        $communityGroupMembers = QueryBuilder::for($communityGroup->members())
            ->cursorPaginate($request->query('per_page', 10));

        return new CommunityGroupMemberCollection($communityGroupMembers);
    }

    /**
     * Create a community group member
     */
    public function store(CommunityGroup $communityGroup, StoreCommunityGroupMemberRequest $request)
    {
        $communityGroup->members()->attach($request->safe()->only('user_id'));

        $communityGroupMember = $communityGroup
            ->members()
            ->wherePivot('user_id', $request->safe()->only('user_id'))
            ->first();

        return new CommunityGroupMemberResource($communityGroupMember);
    }

    /**
     * Retrieve a community group member
     */
    public function show(CommunityGroupMember $communityGroupMember)
    {
        $communityGroupMemberId = $communityGroupMember->id;
        $communityGroupMember = $communityGroupMember
            ->communityGroup
            ->members()
            ->wherePivot('id', $communityGroupMemberId);

        $communityGroupMember = QueryBuilder::for($communityGroupMember)
            ->firstOrFail();

        return new CommunityGroupMemberResource($communityGroupMember);
    }

    /**
     * Update a community group member
     */
    public function update(UpdateCommunityGroupMemberRequest $request, CommunityGroupMember $communityGroupMember)
    {
        $communityGroupMemberId = $communityGroupMember->id;
        $communityGroup = $communityGroupMember->communityGroup;

        $communityGroupMember->update($request->validated());
        $communityGroupMember = $communityGroup
            ->members()
            ->wherePivot('id', $communityGroupMemberId)
            ->firstOrFail();

        return new CommunityGroupMemberResource($communityGroupMember);
    }

    /**
     * Delete a community group member
     */
    public function destroy(CommunityGroupMember $communityGroupMember)
    {
        $communityGroupMemberId = $communityGroupMember->id;
        $communityGroup = $communityGroupMember->communityGroup;
        $communityGroupMember = $communityGroup
            ->members()
            ->wherePivot('id', $communityGroupMemberId)
            ->firstOrFail();

        $communityGroup->members()->detach($communityGroupMember->id);

        return new CommunityGroupMemberResource($communityGroupMember);
    }
}
