<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserPersona\StoreUserPersonaRequest;
use App\Http\Requests\UserPersona\UpdateUserPersonaRequest;
use App\Http\Resources\UserPersona\UserPersonaCollection;
use App\Http\Resources\UserPersona\UserPersonaResource;
use App\Models\User;
use App\Models\UserPersona;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group User Personas
 * Manage user personas.
 */
class UserPersonaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,'.UserPersona::class)->only('store');
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
     */
    public function index(User $user, Request $request)
    {
        $userPersona = QueryBuilder::for($user->groups())
            ->cursorPaginate($request->query('per_page', 10));

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
        $user->personas()->attach($request->safe()->only('persona_id'));

        $userPersona = $user
            ->personas()
            ->wherePivot('persona_id', $request->safe()->only('persona_id'))
            ->first();

        return new UserPersonaResource($userPersona);
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
