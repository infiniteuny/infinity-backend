<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Persona\StorePersonaRequest;
use App\Http\Requests\Persona\UpdatePersonaRequest;
use App\Http\Resources\Persona\PersonaCollection;
use App\Http\Resources\Persona\PersonaResource;
use App\Models\Persona;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Personas
 * Manage personas.
 */
class PersonaController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Persona::class, 'persona');
    }

    /**
     * List all personas
     *
     * @apiResourceCollection App\Http\Resources\Persona\PersonaCollection
     *
     * @apiResourceModel App\Models\Persona
     */
    public function index(Request $request)
    {
        $personas = QueryBuilder::for(Persona::class)
            ->allowedFields([
                'id',
                'name',
                'priority',
                'description',
                'logo',
                'created_at',
                'updated_at',
            ])
            ->allowedFilters([
                'name',
                AllowedFilter::exact('priority'),
                'description',
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ])
            ->allowedSorts([
                'id',
                'name',
                'priority',
                'created_at',
                'updated_at',
            ])
            ->defaultSorts([
                'priority',
                '-id',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new PersonaCollection($personas);
    }

    /**
     * Create a persona
     *
     * @apiResource App\Http\Resources\Persona\PersonaResource
     *
     * @apiResourceModel App\Models\Persona
     */
    public function store(StorePersonaRequest $request)
    {
        $persona = Persona::create($request->validated());

        return new PersonaResource($persona);
    }

    /**
     * Retrieve a persona
     *
     * @apiResource App\Http\Resources\Persona\PersonaResource
     *
     * @apiResourceModel App\Models\Persona
     */
    public function show(Persona $persona)
    {
        $persona = QueryBuilder::for(Persona::where('id', $persona->id))
            ->allowedFields([
                'id',
                'name',
                'priority',
                'description',
                'logo',
                'created_at',
                'updated_at',
            ])
            ->firstOrFail();

        return new PersonaResource($persona);
    }

    /**
     * Update a persona
     *
     * @apiResource App\Http\Resources\Persona\PersonaResource
     *
     * @apiResourceModel App\Models\Persona
     */
    public function update(UpdatePersonaRequest $request, Persona $persona)
    {
        $persona->update($request->validated());

        return new PersonaResource($persona);
    }

    /**
     * Delete a persona
     *
     * @apiResource App\Http\Resources\Persona\PersonaResource
     *
     * @apiResourceModel App\Models\Persona
     */
    public function destroy(Persona $persona)
    {
        $persona->delete();

        return new PersonaResource($persona);
    }
}
