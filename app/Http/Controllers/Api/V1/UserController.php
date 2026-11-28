<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Jobs\CreateSsoUser;
use App\Jobs\DeleteBlob;
use App\Jobs\DeleteSsoUser;
use App\Jobs\UpdateSsoUser;
use App\Models\Config;
use App\Models\Group;
use App\Models\Token;
use App\Models\User;
use App\Traits\HasPagination;
use App\Traits\HasQueryFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

/**
 * @group Users
 * Manage users.
 */
class UserController extends Controller
{
    use HasPagination, HasQueryFilters;

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
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `sso_id`, `name`, `username`, `email_address`, `phone_number`, `student_id`, `major_id`, `links`, `start_date`, `end_date`, `is_member`, `is_extraordinary`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `major`, `major.degree`, `major.faculty`, `personas`, `groups`, `permissions`. (`groups` and `permissions` require authentication). No-example
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
    public function index(Request $request)
    {
        $user = Auth::guard(config('auth.defaults.semi_public_guard'))->user();

        if ($user) {
            $includes = [
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
            ];
            $filters = [
                AllowedFilter::exact('sso_id'),
                'name',
                'username',
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
            ];
            $sorts = [
                'id',
                'name',
                'username',
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
            ];
        } else {
            $includes = [
                'major',
                'major.degree',
                'major.faculty',
                'personas',
            ];
            $filters = [
                'name',
                'username',
                $this->exactCaseInsensitiveFilter('email_address'),
                $this->exactCaseInsensitiveFilter('student_id'),
                AllowedFilter::exact('major_id'),
                AllowedFilter::operator('start_date', FilterOperator::DYNAMIC),
                AllowedFilter::operator('end_date', FilterOperator::DYNAMIC),
                AllowedFilter::exact('is_member'),
                AllowedFilter::exact('is_extraordinary'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ];
            $sorts = [
                'id',
                'name',
                'username',
                'major_id',
                'start_date',
                'end_date',
                'is_member',
                'is_extraordinary',
                'created_at',
                'updated_at',
            ];
        }

        $query = QueryBuilder::for(User::class)
            ->allowedFields(
                'id',
                'sso_id',
                'name',
                'username',
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
            ->allowedIncludes(...$includes)
            ->allowedFilters(...$filters)
            ->allowedSorts(...$sorts)
            ->defaultSorts('-id');

        $users = $this->cursorPaginateWithTieBreaker($query, $request);

        return new UserCollection($users);
    }

    /**
     * Create a user
     *
     * @apiResource App\Http\Resources\User\UserResource status=201
     *
     * @apiResourceModel App\Models\User
     *
     * @bodyParam links object optional An object of external profile or social media links for the user. Example: {"github": "infiniteuny", "linkedin": "infiniteuny", "website": "https://www.infiniteuny.id"}
     * @bodyParam links.* string optional The value or URL of an individual external profile or social media link. Example: https://example.com
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['links'] = (object) ($data['links'] ?? []);
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
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `sso_id`, `name`, `username`, `email_address`, `phone_number`, `student_id`, `major_id`, `links`, `start_date`, `end_date`, `is_member`, `is_extraordinary`, `created_at`, `updated_at`. No-example
     * @queryParam includes string Comma-separated list of relationships to include. Allowed values: `major`, `major.degree`, `major.faculty`, `personas`, `groups`, `permissions`. (`groups` and `permissions` require authentication). No-example
     */
    public function show(User $user)
    {
        $authUser = Auth::guard(config('auth.defaults.semi_public_guard'))->user();

        if ($authUser) {
            $includes = [
                'major',
                'major.degree',
                'major.faculty',
                'personas',
                'groups',
                'permissions',
            ];
        } else {
            $includes = [
                'major',
                'major.degree',
                'major.faculty',
                'personas',
            ];
        }

        $user = QueryBuilder::for(User::where('id', $user->id))
            ->allowedFields(
                'id',
                'sso_id',
                'name',
                'username',
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
            ->allowedIncludes(...$includes)
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
     * @bodyParam links object optional An object of external profile or social media links for the user. Example: {"github": "infiniteuny", "linkedin": "infiniteuny", "website": "https://www.infiniteuny.id"}
     * @bodyParam links.* string optional The value or URL of an individual external profile or social media link. Example: https://example.com
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (array_key_exists('links', $data)) {
            $data['links'] = (object) ($data['links'] ?? []);
        }

        $oldEmail = $user->email_address;
        $wasMember = (bool) $user->is_member;

        DB::transaction(function () use ($user, $data, $oldEmail, $wasMember) {
            $user->update($data);

            if ($oldEmail !== $user->email_address) {
                $groupIds = $user->groups()->pluck('groups.id')->all();
                if ($groupIds !== []) {
                    Group::whereIn('id', $groupIds)->update(['updated_at' => now()]);
                }
            }

            if ($wasMember && ! $user->is_member) {
                Token::where('user_id', $user->id)->delete();
            }
        });

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
        $userId = $user->id;
        $ssoId = $user->sso_id;
        $blobManifests = [];

        DB::transaction(function () use ($user, &$blobManifests) {
            $groupIds = $user->groups()->pluck('groups.id')->all();

            foreach (['core_team_members', 'community_group_admin_members'] as $pivotTable) {
                $rows = DB::table($pivotTable)
                    ->where('user_id', $user->id)
                    ->get(['photo', 'animation']);

                foreach ($rows as $row) {
                    foreach ([$row->photo, $row->animation] as $manifest) {
                        if (is_string($manifest) && $manifest !== '') {
                            $blobManifests[$manifest] = true;
                        }
                    }
                }
            }

            $user->delete();

            if ($groupIds !== []) {
                Group::whereIn('id', $groupIds)->update(['updated_at' => now()]);
            }
        });

        if (is_string($ssoId) && $ssoId !== '') {
            DeleteSsoUser::dispatch($ssoId, $userId);
        }

        foreach (array_keys($blobManifests) as $manifest) {
            DeleteBlob::dispatch($manifest);
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
    public function extendMembership(Request $request, User $user)
    {
        $authUser = $request->user();

        if ($authUser->id !== $user->id && ! $authUser->can('manage-user-membership')) {
            throw new AccessDeniedHttpException('Extending other user\'s membership is not allowed.');
        }

        if (! $user->is_member) {
            throw new AccessDeniedHttpException('User is not a member.');
        }

        $allowReregistration = Config::where('key', 'allow_reregistration')->first();
        $startPeriod = Config::where('key', 'start_reregistration_date')->first();
        $endPeriod = Config::where('key', 'end_reregistration_date')->first();
        $now = Carbon::now();
        $today = Carbon::today()->toDateString();

        if (! $startPeriod || ! $endPeriod) {
            throw new AccessDeniedHttpException('Reregistration period is not configured.');
        }

        $startRaw = $startPeriod->value;
        $endRaw = $endPeriod->value;

        if (! is_string($startRaw) || trim($startRaw) === '' || ! is_string($endRaw) || trim($endRaw) === '') {
            throw new AccessDeniedHttpException('Reregistration period is not configured.');
        }

        $startPeriodRaw = trim($startRaw);
        $endPeriodRaw = trim($endRaw);

        try {
            $parsedStart = Carbon::parse($startPeriodRaw);
            $parsedEnd = Carbon::parse($endPeriodRaw);
        } catch (Throwable) {
            throw new AccessDeniedHttpException('Reregistration period is not configured.');
        }

        $startWindow = preg_match('/^\d{4}-\d{2}-\d{2}$/', $startPeriodRaw)
            ? $parsedStart->copy()->startOfDay()
            : $parsedStart->copy();
        $endWindow = preg_match('/^\d{4}-\d{2}-\d{2}$/', $endPeriodRaw)
            ? $parsedEnd->copy()->endOfDay()
            : $parsedEnd->copy();

        if (! $allowReregistration || $allowReregistration->value !== 'true' || $now->lt($startWindow) || $now->gt($endWindow)) {
            throw new AccessDeniedHttpException('Reregistration is currently disabled.');
        }

        $currentEndDate = $user->end_date
            ? Carbon::parse($user->end_date)->toDateString()
            : null;

        if ($currentEndDate !== null) {
            $allowExpiredReregistration = Config::where('key', 'allow_expired_reregistration')->first();

            if (
                (! $allowExpiredReregistration || $allowExpiredReregistration->value !== 'true')
                && $currentEndDate < $today
            ) {
                throw new AccessDeniedHttpException('Membership has expired and expired member\'s reregistration is not allowed.');
            }
        }

        $candidateEndDate = $parsedEnd->copy()->addYear()->toDateString();
        $newEndDate = ($currentEndDate !== null && $currentEndDate > $candidateEndDate)
            ? $currentEndDate
            : $candidateEndDate;

        $updates = ['end_date' => $newEndDate];

        if (! $user->start_date) {
            $updates['start_date'] = min($today, $newEndDate);
        }

        $user->update($updates);

        if ($user->sso_id) {
            UpdateSsoUser::dispatch($user);
        } else {
            CreateSsoUser::dispatch($user);
        }

        return new UserResource($user);
    }
}
