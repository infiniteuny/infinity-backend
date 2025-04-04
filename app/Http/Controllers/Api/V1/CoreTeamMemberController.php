<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StorageVisibility;
use App\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\CoreTeamMember\StoreCoreTeamMemberRequest;
use App\Http\Requests\CoreTeamMember\UpdateCoreTeamMemberRequest;
use App\Http\Resources\CoreTeamMember\CoreTeamMemberCollection;
use App\Http\Resources\CoreTeamMember\CoreTeamMemberResource;
use App\Jobs\DeleteBlob;
use App\Models\CoreTeam;
use App\Models\CoreTeamMember;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Core Team Member
 * Manage core team members.
 */
class CoreTeamMemberController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CoreTeamMember::class, 'core_team_member');
    }

    /**
     * List all core team members
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\CoreTeamMember\CoreTeamMemberCollection
     *
     * @apiResourceModel App\Models\User states=pivotCoreTeamMember paginate=10,cursor
     */
    public function index(CoreTeam $coreTeam, Request $request)
    {
        $coreTeamMembers = QueryBuilder::for($coreTeam->members())
            ->cursorPaginate($request->query('per_page', 10));

        return new CoreTeamMemberCollection($coreTeamMembers);
    }

    /**
     * Create a core team member
     *
     * @apiResource App\Http\Resources\CoreTeamMember\CoreTeamMemberResource status=201
     *
     * @apiResourceModel App\Models\User states=pivotCoreTeamMember
     */
    public function store(CoreTeam $coreTeam, StoreCoreTeamMemberRequest $request)
    {
        $photoManifest = Storage::store(
            $request->file('photo'),
            'core-teams/photos',
            StorageVisibility::PUBLIC,
        );

        $hasAnimation = $request->has('animation');

        if ($hasAnimation) {
            $animationManifest = Storage::store(
                $request->file('animation'),
                'core-teams/animations',
                StorageVisibility::PUBLIC,
            );
        }

        $coreTeam->members()->attach($request->safe()->only('user_id'), [
            'core_team_division_id' => $request->safe()->only('core_team_division_id'),
            'photo' => $photoManifest,
            'animation' => $hasAnimation ? $animationManifest : null,
        ]);

        $coreTeamMember = $coreTeam
            ->members()
            ->wherePivot('user_id', $request->safe()->only('user_id'))
            ->first();

        return new CoreTeamMemberResource($coreTeamMember);
    }

    /**
     * Retrieve a core team member
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\CoreTeamMember\CoreTeamMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotCoreTeamMember
     */
    public function show(CoreTeamMember $coreTeamMember)
    {
        $coreTeamMemberId = $coreTeamMember->id;
        $coreTeamMember = $coreTeamMember
            ->coreTeam
            ->members()
            ->wherePivot('id', $coreTeamMemberId);

        $coreTeamMember = QueryBuilder::for($coreTeamMember)
            ->firstOrFail();

        return new CoreTeamMemberResource($coreTeamMember);
    }

    /**
     * Update a core team member
     *
     * @apiResource App\Http\Resources\CoreTeamMember\CoreTeamMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotCoreTeamMember
     */
    public function update(UpdateCoreTeamMemberRequest $request, CoreTeamMember $coreTeamMember)
    {
        $coreTeamMemberId = $coreTeamMember->id;
        $coreTeam = $coreTeamMember->coreTeam;

        $hasPhoto = $request->has('image');
        $hasAnimation = $request->has('animation');
        $hasFileAnimation = $request->hasFile('animation');

        if ($hasPhoto) {
            $photoManifest = $coreTeamMember->getRawOriginal('photo');

            dispatch(new DeleteBlob($photoManifest));

            $photoManifest = Storage::store(
                $request->file('photo'),
                'core-teams/photos',
                StorageVisibility::PUBLIC,
            );
        }

        if ($hasAnimation) {
            $animationManifest = $coreTeamMember->getRawOriginal('animation');

            dispatch(new DeleteBlob($animationManifest));

            if ($hasFileAnimation) {
                $animationManifest = Storage::store(
                    $request->file('animation'),
                    'core-teams/animations',
                    StorageVisibility::PUBLIC,
                );
            } else {
                $animationManifest = null;
            }
        }

        $coreTeamMember->update(
            $hasPhoto || $hasAnimation
                ? array_replace($request->validated(), [
                    ...($hasPhoto ? ['photo' => $photoManifest] : []),
                    ...($hasAnimation ? ['animation' => $animationManifest] : []),
                ])
                : $request->validated()
        );
        $coreTeamMember = $coreTeam
            ->members()
            ->wherePivot('id', $coreTeamMemberId)
            ->firstOrFail();

        return new CoreTeamMemberResource($coreTeamMember);
    }

    /**
     * Delete a core team member
     *
     * @apiResource App\Http\Resources\CoreTeamMember\CoreTeamMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotCoreTeamMember
     */
    public function destroy(CoreTeamMember $coreTeamMember)
    {
        $coreTeamMemberId = $coreTeamMember->id;
        $coreTeam = $coreTeamMember->coreTeam;
        $coreTeamMember = $coreTeam
            ->members()
            ->wherePivot('id', $coreTeamMemberId)
            ->firstOrFail();

        $photoManifest = $coreTeamMember->getRawOriginal('photo');
        $animationManifest = $coreTeamMember->getRawOriginal('animation');

        dispatch(new DeleteBlob($photoManifest));
        dispatch(new DeleteBlob($animationManifest));

        $coreTeam->members()->detach($coreTeamMember->id);

        return new CoreTeamMemberResource($coreTeamMember);
    }
}
