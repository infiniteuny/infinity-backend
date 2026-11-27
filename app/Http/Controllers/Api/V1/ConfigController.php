<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\StoreConfigRequest;
use App\Http\Requests\Config\UpdateConfigRequest;
use App\Http\Resources\Config\ConfigCollection;
use App\Http\Resources\Config\ConfigResource;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Enums\FilterOperator;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group Configs
 * Manage configurations.
 */
class ConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,'.Config::class)->only('store');
        $this->middleware('can:update,config')->only('update');
        $this->middleware('can:delete,config')->only('destroy');
    }

    /**
     * List all configurations
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\Config\ConfigCollection
     *
     * @apiResourceModel App\Models\Config paginate=10,cursor
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `key`, `value`, `type`, `is_private`, `created_at`, `updated_at`. No-example
     * @queryParam filters[key] string Filter by partial `key`. No-example
     * @queryParam filters[type] string Filter by exact `type`. No-example
     * @queryParam filters[is_private] boolean Filter by exact `is_private`. Requires authentication. No-example
     * @queryParam filters[created_at] string Filter by `created_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam filters[updated_at] string Filter by `updated_at` using dynamic operators (e.g. `>`, `>=`, `<`, `<=`, `<>`). No-example
     * @queryParam sorts string Comma-separated list of fields to sort by. Prefix with `-` for descending order. Allowed values: `id`, `key`, `type`, `is_private`, `created_at`, `updated_at`. Default: `-id`. (`is_private` requires authentication). No-example
     * @queryParam per_page integer Number of items to return per page. Defaults to 10. Example: 10
     * @queryParam cursor string Cursor token for pagination. No-example
     */
    public function index(Request $request)
    {
        $user = Auth::guard(config('auth.defaults.semi_public_guard'))->user();

        if ($user) {
            $filters = [
                'key',
                AllowedFilter::exact('type'),
                AllowedFilter::exact('is_private'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ];
            $sorts = [
                'id',
                'key',
                'type',
                'is_private',
                'created_at',
                'updated_at',
            ];
        } else {
            $filters = [
                'key',
                AllowedFilter::exact('type'),
                AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
            ];
            $sorts = [
                'id',
                'key',
                'type',
                'created_at',
                'updated_at',
            ];
        }

        $configs = QueryBuilder::for(Config::class)
            ->allowedFields(
                'id',
                'key',
                'value',
                'type',
                'is_private',
                'created_at',
                'updated_at',
            )
            ->allowedFilters(...$filters)
            ->allowedSorts(...$sorts)
            ->defaultSorts('-id');

        if (! $user) {
            $configs = $configs->where('is_private', false);
        }

        $configs = $configs->cursorPaginate($request->query('per_page', 10));

        return new ConfigCollection($configs);
    }

    /**
     * Create a configuration
     *
     * @apiResource App\Http\Resources\Config\ConfigResource status=201
     *
     * @apiResourceModel App\Models\Config
     */
    public function store(StoreConfigRequest $request)
    {
        $config = Config::create($request->validated());

        return new ConfigResource($config);
    }

    /**
     * Retrieve a configuration
     *
     * @unauthenticated
     *
     * @apiResource App\Http\Resources\Config\ConfigResource
     *
     * @apiResourceModel App\Models\Config
     *
     * @queryParam fields string Comma-separated list of fields to include. Allowed values: `id`, `key`, `value`, `type`, `is_private`, `created_at`, `updated_at`. No-example
     */
    public function show(Config $config)
    {
        $user = Auth::guard(config('auth.defaults.semi_public_guard'))->user();

        if ($config->is_private && ! $user) {
            // For security reasons, we don't want to expose private configurations to
            // unauthenticated users nor give them a hint that the configuration exists.
            throw new NotFoundHttpException;
        }

        $config = QueryBuilder::for(Config::where('id', $config->id))
            ->allowedFields(
                'id',
                'key',
                'value',
                'type',
                'is_private',
                'created_at',
                'updated_at',
            )
            ->firstOrFail();

        return new ConfigResource($config);
    }

    /**
     * Update a configuration
     *
     * @apiResource App\Http\Resources\Config\ConfigResource
     *
     * @apiResourceModel App\Models\Config
     */
    public function update(UpdateConfigRequest $request, Config $config)
    {
        $config->update($request->validated());

        return new ConfigResource($config);
    }

    /**
     * Delete a configuration
     *
     * @apiResource App\Http\Resources\Config\ConfigResource
     *
     * @apiResourceModel App\Models\Config
     */
    public function destroy(Config $config)
    {
        $config->delete();

        return new ConfigResource($config);
    }
}
