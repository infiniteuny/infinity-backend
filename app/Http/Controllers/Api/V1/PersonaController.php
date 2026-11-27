<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StorageVisibility;
use App\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Persona\StorePersonaRequest;
use App\Http\Requests\Persona\UpdatePersonaRequest;
use App\Http\Resources\Persona\PersonaCollection;
use App\Http\Resources\Persona\PersonaResource;
use App\Jobs\DeleteBlob;
use App\Models\Persona;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

/**
 * @group Personas
 * Manage personas.
 */
class PersonaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,'.Persona::class)->only('store');
        $this->middleware('can:update,persona')->only('update');
        $this->middleware('can:delete,persona')->only('destroy');
    }

    /**
     * List all personas
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\Persona\PersonaCollection
     *
     * @apiResourceModel App\Models\Persona paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `priority`, `description`, `logo`, `created_at`, `updated_at`. No-example
     * @queryParam filters[name] string Filter by partial `name`. No-example
     * @queryParam filters[priority] integer Filter by exact `priority`. No-example
     * @queryParam filters[description] string Filter by partial `description`. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `name`, `priority`, `created_at`, `updated_at`. Default: `priority`, `-id`. No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $personas = QueryBuilder::for(Persona::class)
            ->allowedFields(
                'id',
                'name',
                'priority',
                'description',
                'logo',
                'created_at',
                'updated_at',
            )
            ->allowedFilters(
                'name',
                AllowedFilter::exact('priority'),
                'description',
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            )
            ->allowedSorts(
                'id',
                'name',
                'priority',
                'created_at',
                'updated_at',
            )
            ->defaultSorts(
                'priority',
                '-id',
            )
            ->cursorPaginate($request->query('per_page', 10));

        return new PersonaCollection($personas);
    }

    /**
     * Create a persona
     *
     * @apiResource App\Http\Resources\Persona\PersonaResource status=201
     *
     * @apiResourceModel App\Models\Persona
     */
    public function store(StorePersonaRequest $request)
    {
        $manifest = Storage::store(
            $request->file('logo'),
            'personas/logos',
            StorageVisibility::PUBLIC,
        );

        try {
            $persona = Persona::create(
                array_replace($request->validated(), ['logo' => $manifest])
            );
        } catch (Throwable $e) {
            if (is_string($manifest) && $manifest !== '') {
                DeleteBlob::dispatch($manifest);
            }

            throw $e;
        }

        return new PersonaResource($persona);
    }

    /**
     * Retrieve a persona
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\Persona\PersonaResource
     *
     * @apiResourceModel App\Models\Persona
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `name`, `priority`, `description`, `logo`, `created_at`, `updated_at`. No-example
     */
    public function show(Persona $persona)
    {
        $persona = QueryBuilder::for(Persona::where('id', $persona->id))
            ->allowedFields(
                'id',
                'name',
                'priority',
                'description',
                'logo',
                'created_at',
                'updated_at',
            )
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
        $hasLogo = $request->has('logo');
        $oldManifest = null;
        $manifest = null;

        if ($hasLogo) {
            $oldManifest = $persona->getRawOriginal('logo');
            $manifest = Storage::store(
                $request->file('logo'),
                'personas/logos',
                StorageVisibility::PUBLIC,
            );
        }

        try {
            $persona->update(
                $hasLogo
                    ? array_replace($request->validated(), ['logo' => $manifest])
                    : $request->validated()
            );
        } catch (Throwable $e) {
            if (is_string($manifest) && $manifest !== '') {
                DeleteBlob::dispatch($manifest);
            }

            throw $e;
        }

        if (is_string($oldManifest) && $oldManifest !== '') {
            DeleteBlob::dispatch($oldManifest);
        }

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
        $manifest = $persona->getRawOriginal('logo');

        $persona->delete();

        if (is_string($manifest) && $manifest !== '') {
            DeleteBlob::dispatch($manifest);
        }

        return new PersonaResource($persona);
    }
}
