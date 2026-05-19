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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\Includes\IncludeInterface;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Community Group Admin Members
 * Manage community group administrator members.
 */
class CommunityGroupAdminMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,'.CommunityGroupAdminMember::class)->only('store');
        $this->middleware('can:update,community_group_admin_member')->only('update');
        $this->middleware('can:delete,community_group_admin_member')->only('destroy');
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
            ->allowedIncludes(
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
                AllowedInclude::custom('membership.community_group', new class implements IncludeInterface
                {
                    public function __invoke(Builder $query, string $include): void
                    {
                        // Handled manually after pagination
                    }
                }),
            )
            ->allowedFilters(
                AllowedFilter::exact('sso_id', 'users.sso_id'),
                AllowedFilter::partial('name', 'users.name'),
                AllowedFilter::partial('email_address', 'users.email_address'),
                AllowedFilter::partial('phone_number', 'users.phone_number'),
                AllowedFilter::partial('student_id', 'users.student_id'),
                AllowedFilter::exact('major_id', 'users.major_id'),
                AllowedFilter::operator('start_date', FilterOperator::DYNAMIC, 'and', 'users.start_date'),
                AllowedFilter::operator('end_date', FilterOperator::DYNAMIC, 'and', 'users.end_date'),
                AllowedFilter::exact('is_member', 'users.is_member'),
                AllowedFilter::exact('is_extraordinary', 'users.is_extraordinary'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC, 'and', 'users.created_at'),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC, 'and', 'users.updated_at'),
            )
            ->allowedSorts(
                AllowedSort::field('id', 'users.id'),
                AllowedSort::field('name', 'users.name'),
                AllowedSort::field('email_address', 'users.email_address'),
                AllowedSort::field('phone_number', 'users.phone_number'),
                AllowedSort::field('student_id', 'users.student_id'),
                AllowedSort::field('major_id', 'users.major_id'),
                AllowedSort::field('start_date', 'users.start_date'),
                AllowedSort::field('end_date', 'users.end_date'),
                AllowedSort::field('is_member', 'users.is_member'),
                AllowedSort::field('is_extraordinary', 'users.is_extraordinary'),
                AllowedSort::field('created_at', 'users.created_at'),
                AllowedSort::field('updated_at', 'users.updated_at'),
            )
            ->defaultSorts('-users.id')
            ->cursorPaginate($request->query('per_page', 10));

        if (in_array('membership.community_group', explode(',', $request->query('includes', '')))) {
            Collection::make(
                $communityGroupAdminMembers->getCollection()->pluck('membership')->filter()
            )->load('communityGroup');
        }

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

        $communityGroupAdmin->members()->attach($request->validated('user_id'), [
            'community_group_id' => $request->validated('community_group_id'),
            'photo' => $photoManifest,
            'animation' => $hasAnimation ? $animationManifest : null,
        ]);

        $communityGroupAdminMember = $communityGroupAdmin
            ->members()
            ->wherePivot('user_id', $request->validated('user_id'))
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
            ->allowedIncludes(
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
                AllowedInclude::custom('membership.community_group', new class implements IncludeInterface
                {
                    public function __invoke(Builder $query, string $include): void
                    {
                        // Handled manually
                    }
                }),
            )
            ->firstOrFail();

        if (in_array('membership.community_group', explode(',', request()->query('includes', '')))) {
            if ($communityGroupAdminMember->membership) {
                $communityGroupAdminMember->membership->load('communityGroup');
            }
        }

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

            DeleteBlob::dispatch($oldPhotoManifest);

            $photoManifest = Storage::store(
                $request->file('photo'),
                'community-group-admins/photos',
                StorageVisibility::PUBLIC,
            );
        }

        if ($hasAnimation) {
            $oldAnimationManifest = $communityGroupAdminMember->getRawOriginal('animation');

            if ($oldAnimationManifest) {
                DeleteBlob::dispatch($oldAnimationManifest);
            }

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
        $photoManifest = $communityGroupAdminMember->getRawOriginal('photo');
        $animationManifest = $communityGroupAdminMember->getRawOriginal('animation');

        DeleteBlob::dispatch($photoManifest);

        if ($animationManifest) {
            DeleteBlob::dispatch($animationManifest);
        }

        $communityGroupAdminMemberId = $communityGroupAdminMember->id;
        $communityGroupAdmin = $communityGroupAdminMember->communityGroupAdmin;
        $communityGroupAdminMember = $communityGroupAdmin
            ->members()
            ->wherePivot('id', $communityGroupAdminMemberId)
            ->firstOrFail();

        $communityGroupAdmin->members()->detach($communityGroupAdminMember->id);

        return new CommunityGroupAdminMemberResource($communityGroupAdminMember);
    }
}
