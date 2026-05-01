<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCommunityGroup\UserCommunityGroupCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group User Community Groups
 * Manage user community groups.
 */
class UserCommunityGroupController extends Controller
{
    /**
     * List all user community groups
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\UserCommunityGroup\UserCommunityGroupCollection
     *
     * @apiResourceModel App\Models\CommunityGroup states=pivotCommunityGroupMember paginate=10,cursor
     */
    public function index(User $user, Request $request)
    {
        $userCommunityGroups = QueryBuilder::for($user->communityGroups())
            ->allowedFilters(
                AllowedFilter::partial('name', 'community_groups.name'),
                AllowedFilter::exact('priority', 'community_groups.priority'),
                AllowedFilter::partial('description', 'community_groups.description'),
                AllowedFilter::exact('is_active', 'community_groups.is_active'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC, 'and', 'community_groups.created_at'),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC, 'and', 'community_groups.updated_at'),
            )
            ->allowedSorts(
                AllowedSort::field('id', 'community_groups.id'),
                AllowedSort::field('name', 'community_groups.name'),
                AllowedSort::field('priority', 'community_groups.priority'),
                AllowedSort::field('is_active', 'community_groups.is_active'),
                AllowedSort::field('created_at', 'community_groups.created_at'),
                AllowedSort::field('updated_at', 'community_groups.updated_at'),
            )
            ->defaultSort(
                'community_groups.priority',
                '-community_groups.id',
            )
            ->cursorPaginate($request->query('per_page', 10));

        return new UserCommunityGroupCollection($userCommunityGroups);
    }
}
