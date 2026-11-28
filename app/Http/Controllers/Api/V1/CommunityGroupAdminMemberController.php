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
use App\Traits\HasPagination;
use App\Traits\HasQueryFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\Includes\IncludeInterface;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

/**
 * @group Community Group Admin Members
 * Manage community group administrator members.
 */
class CommunityGroupAdminMemberController extends Controller
{
    use HasPagination, HasQueryFilters;

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
     *
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `major`, `major.degree`, `major.faculty`, `personas`, `groups`, `permissions`, `membership.community_group`. (`groups` and `permissions` require authentication). No-example
     * @queryParam filters[sso_id] string Filter by exact `sso_id`. Requires authentication. No-example
     * @queryParam filters[name] string Filter by partial `name`. No-example
     * @queryParam filters[username] string Filter by partial `username`. No-example
     * @queryParam filters[email_address] string Filter by `email_address` (single-value case-insensitive exact match when unauthenticated; partial match when authenticated). No-example
     * @queryParam filters[phone_number] string Filter by partial `phone_number`. Requires authentication. No-example
     * @queryParam filters[student_id] string Filter by `student_id` (single-value case-insensitive exact match when unauthenticated; partial match when authenticated). No-example
     * @queryParam filters[major_id] string Filter by exact `major_id`. No-example
     * @queryParam filters[start_date] string Filter by `start_date` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[end_date] string Filter by `end_date` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[is_member] boolean Filter by exact `is_member`. No-example
     * @queryParam filters[is_extraordinary] boolean Filter by exact `is_extraordinary`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `name`, `username`, `email_address`, `phone_number`, `student_id`, `major_id`, `start_date`, `end_date`, `is_member`, `is_extraordinary`, `created_at`, `updated_at`. Default: `-id`. (`email_address`, `phone_number`, and `student_id` require authentication). No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(CommunityGroupAdmin $communityGroupAdmin, Request $request)
    {
        $user = Auth::guard(config('auth.defaults.semi_public_guard'))->user();
        $membershipCommunityGroupInclude = AllowedInclude::custom('membership.community_group', new class implements IncludeInterface
        {
            public function __invoke(Builder $query, string $include): void
            {
                // Handled manually after pagination
            }
        });

        if ($user) {
            $includes = [
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
                $membershipCommunityGroupInclude,
            ];
            $filters = [
                AllowedFilter::exact('sso_id', 'users.sso_id'),
                AllowedFilter::partial('name', 'users.name'),
                AllowedFilter::partial('username', 'users.username'),
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
            ];
            $sorts = [
                AllowedSort::field('id', 'users.id'),
                AllowedSort::field('name', 'users.name'),
                AllowedSort::field('username', 'users.username'),
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
            ];
        } else {
            $includes = [
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                $membershipCommunityGroupInclude,
            ];
            $filters = [
                AllowedFilter::partial('name', 'users.name'),
                AllowedFilter::partial('username', 'users.username'),
                $this->exactCaseInsensitiveFilter('email_address', 'users.email_address'),
                $this->exactCaseInsensitiveFilter('student_id', 'users.student_id'),
                AllowedFilter::exact('major_id', 'users.major_id'),
                AllowedFilter::operator('start_date', FilterOperator::DYNAMIC, 'and', 'users.start_date'),
                AllowedFilter::operator('end_date', FilterOperator::DYNAMIC, 'and', 'users.end_date'),
                AllowedFilter::exact('is_member', 'users.is_member'),
                AllowedFilter::exact('is_extraordinary', 'users.is_extraordinary'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC, 'and', 'users.created_at'),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC, 'and', 'users.updated_at'),
            ];
            $sorts = [
                AllowedSort::field('id', 'users.id'),
                AllowedSort::field('name', 'users.name'),
                AllowedSort::field('username', 'users.username'),
                AllowedSort::field('major_id', 'users.major_id'),
                AllowedSort::field('start_date', 'users.start_date'),
                AllowedSort::field('end_date', 'users.end_date'),
                AllowedSort::field('is_member', 'users.is_member'),
                AllowedSort::field('is_extraordinary', 'users.is_extraordinary'),
                AllowedSort::field('created_at', 'users.created_at'),
                AllowedSort::field('updated_at', 'users.updated_at'),
            ];
        }

        $query = QueryBuilder::for($communityGroupAdmin->members())
            ->allowedIncludes(...$includes)
            ->allowedFilters(...$filters)
            ->allowedSorts(...$sorts)
            ->defaultSorts('-users.id')
            ->orderByDesc('community_group_admin_members.id');

        $communityGroupAdminMembers = $this->cursorPaginateWithTieBreaker(
            $query,
            $request,
            ['community_group_admin_members.id'],
        );

        if (in_array('membership.community_group', $this->parseRequestedIncludes($request), true)) {
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
        $uploadedManifests = [];
        $hasFileAnimation = $request->hasFile('animation');

        try {
            $photoManifest = Storage::store(
                $request->file('photo'),
                'community-group-admins/photos',
                StorageVisibility::PUBLIC,
            );
            if (is_string($photoManifest) && $photoManifest !== '') {
                $uploadedManifests[] = $photoManifest;
            }

            $animationManifest = null;
            if ($hasFileAnimation) {
                $animationManifest = Storage::store(
                    $request->file('animation'),
                    'community-group-admins/animations',
                    StorageVisibility::PUBLIC,
                );
                if (is_string($animationManifest) && $animationManifest !== '') {
                    $uploadedManifests[] = $animationManifest;
                }
            }

            $communityGroupAdmin->members()->attach($request->validated('user_id'), [
                'community_group_id' => $request->validated('community_group_id'),
                'photo' => $photoManifest,
                'animation' => $hasFileAnimation ? $animationManifest : null,
            ]);
        } catch (Throwable $e) {
            foreach ($uploadedManifests as $manifest) {
                DeleteBlob::dispatch($manifest);
            }

            throw $e;
        }

        $communityGroupAdminMember = $communityGroupAdmin
            ->members()
            ->wherePivot('user_id', $request->validated('user_id'))
            ->wherePivot('community_group_id', $request->validated('community_group_id'))
            ->first();

        return (new CommunityGroupAdminMemberResource($communityGroupAdminMember))->response()->setStatusCode(201);
    }

    /**
     * Retrieve a community group administrator member
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\CommunityGroupAdminMember\CommunityGroupAdminMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotCommunityGroupAdminMember
     *
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `major`, `major.degree`, `major.faculty`, `personas`, `groups`, `permissions`, `membership.community_group`. (`groups` and `permissions` require authentication). No-example
     */
    public function show(CommunityGroupAdminMember $communityGroupAdminMember)
    {
        $user = Auth::guard(config('auth.defaults.semi_public_guard'))->user();
        $membershipCommunityGroupInclude = AllowedInclude::custom('membership.community_group', new class implements IncludeInterface
        {
            public function __invoke(Builder $query, string $include): void
            {
                // Handled manually
            }
        });

        if ($user) {
            $includes = [
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
                $membershipCommunityGroupInclude,
            ];
        } else {
            $includes = [
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                $membershipCommunityGroupInclude,
            ];
        }

        $communityGroupAdminMemberId = $communityGroupAdminMember->id;
        $communityGroupAdminMember = $communityGroupAdminMember
            ->communityGroupAdmin
            ->members()
            ->wherePivot('id', $communityGroupAdminMemberId);

        $communityGroupAdminMember = QueryBuilder::for($communityGroupAdminMember)
            ->allowedIncludes(...$includes)
            ->firstOrFail();

        if (in_array('membership.community_group', $this->parseRequestedIncludes(request()), true)) {
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

        $hasPhoto = $request->has('photo');
        $hasAnimation = $request->has('animation');
        $hasFileAnimation = $request->hasFile('animation');

        $oldPhotoManifest = $hasPhoto ? $communityGroupAdminMember->getRawOriginal('photo') : null;
        $oldAnimationManifest = $hasAnimation ? $communityGroupAdminMember->getRawOriginal('animation') : null;
        $uploadedManifests = [];
        $photoManifest = null;
        $animationManifest = null;

        try {
            if ($hasPhoto) {
                $photoManifest = Storage::store(
                    $request->file('photo'),
                    'community-group-admins/photos',
                    StorageVisibility::PUBLIC,
                );
                if (is_string($photoManifest) && $photoManifest !== '') {
                    $uploadedManifests[] = $photoManifest;
                }
            }

            if ($hasAnimation && $hasFileAnimation) {
                $animationManifest = Storage::store(
                    $request->file('animation'),
                    'community-group-admins/animations',
                    StorageVisibility::PUBLIC,
                );
                if (is_string($animationManifest) && $animationManifest !== '') {
                    $uploadedManifests[] = $animationManifest;
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
        } catch (Throwable $e) {
            foreach ($uploadedManifests as $manifest) {
                DeleteBlob::dispatch($manifest);
            }

            throw $e;
        }

        if (is_string($oldPhotoManifest) && $oldPhotoManifest !== '') {
            DeleteBlob::dispatch($oldPhotoManifest);
        }

        if (is_string($oldAnimationManifest) && $oldAnimationManifest !== '') {
            DeleteBlob::dispatch($oldAnimationManifest);
        }

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

        $communityGroupAdminMemberId = $communityGroupAdminMember->id;
        $communityGroupAdmin = $communityGroupAdminMember->communityGroupAdmin;
        $member = $communityGroupAdmin
            ->members()
            ->wherePivot('id', $communityGroupAdminMemberId)
            ->firstOrFail();

        $communityGroupAdminMember->delete();

        if (is_string($photoManifest) && $photoManifest !== '') {
            DeleteBlob::dispatch($photoManifest);
        }

        if (is_string($animationManifest) && $animationManifest !== '') {
            DeleteBlob::dispatch($animationManifest);
        }

        return new CommunityGroupAdminMemberResource($member);
    }
}
