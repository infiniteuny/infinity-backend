<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCommunityGroup\UserCommunityGroupCollection;
use App\Models\User;
use App\Traits\HasPagination;
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
    use HasPagination;

    /**
     * List all user community groups
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\UserCommunityGroup\UserCommunityGroupCollection
     *
     * @apiResourceModel App\Models\CommunityGroup states=pivotCommunityGroupMember paginate=10,cursor
     *
     * @queryParam filters[name] string Filter by partial `name`. No-example
     * @queryParam filters[priority] integer Filter by exact `priority`. No-example
     * @queryParam filters[description] string Filter by partial `description`. No-example
     * @queryParam filters[is_active] boolean Filter by exact `is_active`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `name`, `priority`, `is_active`, `created_at`, `updated_at`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
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
            );

        $userCommunityGroups = $this->cursorPaginateWithTieBreaker($userCommunityGroups, $request);

        return new UserCommunityGroupCollection($userCommunityGroups);
    }
}
