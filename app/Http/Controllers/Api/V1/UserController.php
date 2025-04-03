<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Users
 * Manage users.
 */
class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * List all users
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\User\UserCollection
     *
     * @apiResourceModel App\Models\User paginate=10,cursor
     */
    public function index(Request $request)
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFields([
                'id',
                'sso_id',
                'name',
                'email_address',
                'phone_number',
                'student_id',
                'major_id',
                'links',
                'start_date',
                'end_date',
                'is_member',
                'is_extraordinary',
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes([
                'major',
                'personas',
                'groups',
                'permissions',
            ])
            ->allowedFilters([
                AllowedFilter::exact('sso_id'),
                'name',
                'email_address',
                'phone_number',
                'student_id',
                AllowedFilter::exact('major_id'),
                AllowedFilter::operator('start_date', FilterOperator::DYNAMIC),
                AllowedFilter::operator('end_date', FilterOperator::DYNAMIC),
                AllowedFilter::exact('is_member'),
                AllowedFilter::exact('is_extraordinary'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'name',
                'email_address',
                'phone_number',
                'student_id',
                'major_id',
                'start_date',
                'end_date',
                'is_member',
                'is_extraordinary',
                'created_at',
                'updated_at',
            ])
            ->defaultSorts([
                '-id',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new UserCollection($users);
    }

    /**
     * Create a user
     *
     * @apiResource App\Http\Resources\User\UserResource status=201
     *
     * @apiResourceModel App\Models\User
     *
     * @bodyParam links object required Example: {"github": "https://github.com/infiniteuny", "linkedin": "https://linkedin.com/company/infiniteuny/", "website": "https://infiniteuny.id"}
     * @bodyParam links.* string Example: https://example.com
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        return new UserResource($user);
    }

    /**
     * Retrieve a user
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\User\UserResource
     *
     * @apiResourceModel App\Models\User
     */
    public function show(User $user)
    {
        $user = QueryBuilder::for(User::where('id', $user->id))
            ->allowedFields([
                'id',
                'sso_id',
                'name',
                'email_address',
                'phone_number',
                'student_id',
                'major_id',
                'links',
                'start_date',
                'end_date',
                'is_member',
                'is_extraordinary',
                'created_at',
                'updated_at',
            ])
            ->allowedIncludes([
                'major',
                'personas',
                'groups',
                'permissions',
            ])
            ->firstOrFail();

        return new UserResource($user);
    }

    /**
     * Update a user
     *
     * @apiResource App\Http\Resources\User\UserResource
     *
     * @apiResourceModel App\Models\User
     *
     * @bodyParam links object Example: {"github": "https://github.com/infiniteuny", "linkedin": "https://linkedin.com/company/infiniteuny/", "website": "https://infiniteuny.id"}
     * @bodyParam links.* string Example: https://example.com
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return new UserResource($user);
    }

    /**
     * Delete a user
     *
     * @apiResource App\Http\Resources\User\UserResource
     *
     * @apiResourceModel App\Models\User
     */
    public function destroy(User $user)
    {
        $user->delete();

        return new UserResource($user);
    }
}
