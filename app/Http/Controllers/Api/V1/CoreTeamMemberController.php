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
 * @group Core Team Members
 * Manage core team members.
 */
class CoreTeamMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,'.CoreTeamMember::class)->only('store');
        $this->middleware('can:update,core_team_member')->only('update');
        $this->middleware('can:delete,core_team_member')->only('destroy');
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
            ->allowedIncludes(
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
                AllowedInclude::custom('membership.core_team_division', new class implements IncludeInterface
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

        if (in_array('membership.core_team_division', explode(',', $request->query('includes', '')))) {
            Collection::make(
                $coreTeamMembers->getCollection()->pluck('membership')->filter()
            )->load('coreTeamDivision');
        }

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

        $coreTeam->members()->attach($request->validated('user_id'), [
            'core_team_division_id' => $request->validated('core_team_division_id'),
            'photo' => $photoManifest,
            'animation' => $hasAnimation ? $animationManifest : null,
        ]);

        $coreTeamMember = $coreTeam
            ->members()
            ->wherePivot('user_id', $request->validated('user_id'))
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
            ->allowedIncludes(
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
                AllowedInclude::custom('membership.core_team_division', new class implements IncludeInterface
                {
                    public function __invoke(Builder $query, string $include): void
                    {
                        // Handled manually
                    }
                }),
            )
            ->firstOrFail();

        if (in_array('membership.core_team_division', explode(',', request()->query('includes', '')))) {
            if ($coreTeamMember->membership) {
                $coreTeamMember->membership->load('coreTeamDivision');
            }
        }

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

            DeleteBlob::dispatch($photoManifest);

            $photoManifest = Storage::store(
                $request->file('photo'),
                'core-teams/photos',
                StorageVisibility::PUBLIC,
            );
        }

        if ($hasAnimation) {
            $oldAnimationManifest = $coreTeamMember->getRawOriginal('animation');

            if ($oldAnimationManifest) {
                DeleteBlob::dispatch($oldAnimationManifest);
            }

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
        $photoManifest = $coreTeamMember->getRawOriginal('photo');
        $animationManifest = $coreTeamMember->getRawOriginal('animation');

        DeleteBlob::dispatch($photoManifest);

        if ($animationManifest) {
            DeleteBlob::dispatch($animationManifest);
        }

        $coreTeamMemberId = $coreTeamMember->id;
        $coreTeam = $coreTeamMember->coreTeam;
        $coreTeamMember = $coreTeam
            ->members()
            ->wherePivot('id', $coreTeamMemberId)
            ->firstOrFail();

        $coreTeam->members()->detach($coreTeamMember->id);

        return new CoreTeamMemberResource($coreTeamMember);
    }
}
