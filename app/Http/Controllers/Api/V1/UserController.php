<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Jobs\CreateSsoUser;
use App\Jobs\DeleteSsoUser;
use App\Jobs\UpdateSsoUser;
use App\Models\Config;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @group Users
 * Manage users.
 */
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,'.User::class)->only('store');
        $this->middleware('can:update,user')->only('update');
        $this->middleware('can:delete,user')->only('destroy');
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
            ->allowedFields(
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
            )
            ->allowedIncludes(
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
            )
            ->allowedFilters(
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
            )
            ->allowedSorts(
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
            )
            ->defaultSorts('-id')
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
        $data = $request->validated();
        $data['links'] = $data['links'] ?? [];
        $data['is_member'] ??= false;
        $data['is_extraordinary'] ??= false;

        $user = User::create($data);

        CreateSsoUser::dispatch($user);

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
            ->allowedFields(
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
            )
            ->allowedIncludes(
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
            )
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

        if ($user->sso_id) {
            UpdateSsoUser::dispatch($user);
        } else {
            CreateSsoUser::dispatch($user);
        }

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

        if ($user->sso_id) {
            DeleteSsoUser::dispatch($user->sso_id);
        }

        return new UserResource($user);
    }

    /**
     * Extend a user's membership
     *
     * @apiResource App\Http\Resources\User\UserResource
     *
     * @apiResourceModel App\Models\User
     */
    public function extendMembership(User $user)
    {
        if (! $user->is_member) {
            throw new AccessDeniedHttpException('User is not a member.');
        }

        $allowReregistration = Config::where('key', 'allow_reregistration')->first();
        $startPeriod = Config::where('key', 'start_reregistration_date')->first();
        $endPeriod = Config::where('key', 'end_reregistration_date')->first();
        $now = Carbon::now();

        if (! $startPeriod || ! $endPeriod) {
            throw new AccessDeniedHttpException('Reregistration period is not configured.');
        }

        if (! $allowReregistration || $allowReregistration->value !== 'true' || $now->lt(Carbon::parse($startPeriod->value)) || $now->gt(Carbon::parse($endPeriod->value))) {
            throw new AccessDeniedHttpException('Reregistration is currently disabled.');
        }

        if ($user->end_date) {
            $allowExpiredReregistration = Config::where('key', 'allow_expired_reregistration')->first();

            if (
                (! $allowExpiredReregistration || $allowExpiredReregistration->value !== 'true')
                && Carbon::parse($user->end_date)->lt($now)
            ) {
                throw new AccessDeniedHttpException('Membership has expired and expired member\'s reregistration is not allowed.');
            }
        }

        $newEndDate = Carbon::parse($endPeriod->value)->addYear();

        $user->update(['end_date' => $newEndDate->toDateString()]);

        if ($user->sso_id) {
            UpdateSsoUser::dispatch($user);
        } else {
            CreateSsoUser::dispatch($user);
        }

        return new UserResource($user);
    }
}
