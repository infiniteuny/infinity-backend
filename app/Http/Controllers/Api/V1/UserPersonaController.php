<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserPersona\StoreUserPersonaRequest;
use App\Http\Requests\UserPersona\UpdateUserPersonaRequest;
use App\Http\Resources\UserPersona\UserPersonaCollection;
use App\Http\Resources\UserPersona\UserPersonaResource;
use App\Models\User;
use App\Models\UserPersona;
use App\Traits\HasPagination;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group User Personas
 * Manage user personas.
 */
class UserPersonaController extends Controller
{
    use HasPagination;

    public function __construct()
    {
        $this->middleware('can:create,'.UserPersona::class.',user')->only('store');
        $this->middleware('can:update,user_persona')->only('update');
        $this->middleware('can:delete,user_persona')->only('destroy');
    }

    /**
     * List all user personas
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\UserPersona\UserPersonaCollection
     *
     * @apiResourceModel App\Models\Persona states=pivotUserPersona paginate=10,cursor
     *
     * @queryParam filters[name] string Filter by partial `name`. No-example
     * @queryParam filters[priority] integer Filter by exact `priority`. No-example
     * @queryParam filters[description] string Filter by partial `description`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `name`, `priority`, `created_at`, `updated_at`. Default: `priority`, `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(User $user, Request $request)
    {
        $userPersona = QueryBuilder::for($user->personas())
            ->allowedFilters(
                AllowedFilter::partial('name', 'personas.name'),
                AllowedFilter::exact('priority', 'personas.priority'),
                AllowedFilter::partial('description', 'personas.description'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC, 'and', 'personas.created_at'),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC, 'and', 'personas.updated_at'),
            )
            ->allowedSorts(
                AllowedSort::field('id', 'personas.id'),
                AllowedSort::field('name', 'personas.name'),
                AllowedSort::field('priority', 'personas.priority'),
                AllowedSort::field('created_at', 'personas.created_at'),
                AllowedSort::field('updated_at', 'personas.updated_at'),
            )
            ->defaultSorts(
                'personas.priority',
                '-personas.id',
            );

        $userPersona = $this->cursorPaginateWithTieBreaker($userPersona, $request);

        return new UserPersonaCollection($userPersona);
    }

    /**
     * Create a user persona
     *
     * @apiResource App\Http\Resources\UserPersona\UserPersonaResource status=201
     *
     * @apiResourceModel App\Models\Persona states=pivotUserPersona
     */
    public function store(User $user, StoreUserPersonaRequest $request)
    {
        $user->personas()->attach($request->validated('persona_id'));

        $userPersona = $user
            ->personas()
            ->wherePivot('persona_id', $request->validated('persona_id'))
            ->first();

        return (new UserPersonaResource($userPersona))->response()->setStatusCode(201);
    }

    /**
     * Retrieve a user persona
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\UserPersona\UserPersonaResource
     *
     * @apiResourceModel App\Models\Persona states=pivotUserPersona
     */
    public function show(UserPersona $userPersona)
    {
        $userPersonaId = $userPersona->id;
        $userPersona = $userPersona
            ->user
            ->personas()
            ->wherePivot('id', $userPersonaId);

        $userPersona = QueryBuilder::for($userPersona)
            ->firstOrFail();

        return new UserPersonaResource($userPersona);
    }

    /**
     * Update a user persona
     *
     * @apiResource App\Http\Resources\UserPersona\UserPersonaResource
     *
     * @apiResourceModel App\Models\Persona states=pivotUserPersona
     */
    public function update(UpdateUserPersonaRequest $request, UserPersona $userPersona)
    {
        $userPersonaId = $userPersona->id;
        $user = $userPersona->user;

        $userPersona->update($request->validated());
        $userPersona = $user
            ->personas()
            ->wherePivot('id', $userPersonaId)
            ->firstOrFail();

        return new UserPersonaResource($userPersona);
    }

    /**
     * Delete a user persona
     *
     * @apiResource App\Http\Resources\UserPersona\UserPersonaResource
     *
     * @apiResourceModel App\Models\Persona states=pivotUserPersona
     */
    public function destroy(UserPersona $userPersona)
    {
        $userPersonaId = $userPersona->id;
        $user = $userPersona->user;
        $userPersona = $user
            ->personas()
            ->wherePivot('id', $userPersonaId)
            ->firstOrFail();

        $user->personas()->detach($userPersona->id);

        return new UserPersonaResource($userPersona);
    }
}
