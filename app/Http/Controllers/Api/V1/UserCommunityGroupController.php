<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCommunityGroup\UserCommunityGroupCollection;
use App\Models\User;
use Illuminate\Http\Request;
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
            ->cursorPaginate($request->query('per_page', 10));

        return new UserCommunityGroupCollection($userCommunityGroups);
    }
}
