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
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Community Group Members
 * Manage community group members.
 */
class CommunityGroupMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,'.CommunityGroupMember::class)->only('store');
        $this->middleware('can:update,community_group_member')->only('update');
        $this->middleware('can:delete,community_group_member')->only('destroy');
    }

    /**
     * List all community group members
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\CommunityGroupMember\CommunityGroupMemberCollection
     *
     * @apiResourceModel App\Models\User states=pivotCommunityGroupMember paginate=10,cursor
     *
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `major`, `major.degree`, `major.faculty`, `personas`, `groups`, `permissions`. No-example
     * @queryParam filters[sso_id] string Filter by exact `sso_id`. No-example
     * @queryParam filters[name] string Filter by partial `name`. No-example
     * @queryParam filters[email_address] string Filter by partial `email_address`. No-example
     * @queryParam filters[phone_number] string Filter by partial `phone_number`. No-example
     * @queryParam filters[student_id] string Filter by partial `student_id`. No-example
     * @queryParam filters[major_id] string Filter by exact `major_id`. No-example
     * @queryParam filters[start_date] string Filter by `start_date` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[end_date] string Filter by `end_date` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[is_member] boolean Filter by exact `is_member`. No-example
     * @queryParam filters[is_extraordinary] boolean Filter by exact `is_extraordinary`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `name`, `email_address`, `phone_number`, `student_id`, `major_id`, `start_date`, `end_date`, `is_member`, `is_extraordinary`, `created_at`, `updated_at`. Default: `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(CommunityGroup $communityGroup, Request $request)
    {
        $communityGroupMembers = QueryBuilder::for($communityGroup->members())
            ->allowedIncludes(
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
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

        return new CommunityGroupMemberCollection($communityGroupMembers);
    }

    /**
     * Create a community group member
     *
     * @apiResource App\Http\Resources\CommunityGroupMember\CommunityGroupMemberResource status=201
     *
     * @apiResourceModel App\Models\User states=pivotCommunityGroupMember
     */
    public function store(CommunityGroup $communityGroup, StoreCommunityGroupMemberRequest $request)
    {
        $communityGroup->members()->attach($request->validated('user_id'));

        $communityGroupMember = $communityGroup
            ->members()
            ->wherePivot('user_id', $request->validated('user_id'))
            ->first();

        return new CommunityGroupMemberResource($communityGroupMember);
    }

    /**
     * Retrieve a community group member
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\CommunityGroupMember\CommunityGroupMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotCommunityGroupMember
     *
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `major`, `major.degree`, `major.faculty`, `personas`, `groups`, `permissions`. No-example
     */
    public function show(CommunityGroupMember $communityGroupMember)
    {
        $communityGroupMemberId = $communityGroupMember->id;
        $communityGroupMember = $communityGroupMember
            ->communityGroup
            ->members()
            ->wherePivot('id', $communityGroupMemberId);

        $communityGroupMember = QueryBuilder::for($communityGroupMember)
            ->allowedIncludes(
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
            )
            ->firstOrFail();

        return new CommunityGroupMemberResource($communityGroupMember);
    }

    /**
     * Update a community group member
     *
     * @apiResource App\Http\Resources\CommunityGroupMember\CommunityGroupMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotCommunityGroupMember
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
     *
     * @apiResource App\Http\Resources\CommunityGroupMember\CommunityGroupMemberResource
     *
     * @apiResourceModel App\Models\User states=pivotCommunityGroupMember
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
