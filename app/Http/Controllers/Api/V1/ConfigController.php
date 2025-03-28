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
     * @apiResourceCollection App\Http\Resources\Config\ConfigCollection
     *
     * @apiResourceModel App\Models\Config
     */
    public function index(Request $request)
    {
        $configs = QueryBuilder::for(Config::class)
            ->allowedFields([
                'id',
                'key',
                'value',
                'type',
                'is_private',
                'created_at',
                'updated_at',
            ]);

        if (Auth::check()) {
            $configs = $configs
                ->allowedFilters([
                    'key',
                    AllowedFilter::exact('type'),
                    AllowedFilter::exact('is_private'),
                    AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                    AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
                ])
                ->allowedSorts([
                    'id',
                    'key',
                    'type',
                    'is_private',
                    'created_at',
                    'updated_at',
                ]);
        } else {
            $configs = $configs
                ->allowedFilters([
                    'key',
                    AllowedFilter::exact('type'),
                    AllowedFilter::operator('created_at', FilterOperator::DYNAMIC),
                    AllowedFilter::operator('updated_at', FilterOperator::DYNAMIC),
                ])
                ->allowedSorts([
                    'id',
                    'key',
                    'type',
                    'created_at',
                    'updated_at',
                ])
                ->where('is_private', false);
        }

        $configs = $configs
            ->defaultSorts([
                '-id',
            ])
            ->cursorPaginate($request->query('per_page', 10));

        return new ConfigCollection($configs);
    }

    /**
     * Create a configuration
     *
     * @apiResource App\Http\Resources\Config\ConfigResource
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
     * @apiResource App\Http\Resources\Config\ConfigResource
     *
     * @apiResourceModel App\Models\Config
     */
    public function show(Config $config)
    {
        if ($config->is_private && ! Auth::check()) {
            // For security reasons, we don't want to expose private configurations to
            // unauthenticated users nor give them a hint that the configuration exists.
            throw new NotFoundHttpException;
        }

        $config = QueryBuilder::for(Config::where('id', $config->id))
            ->allowedFields([
                'id',
                'key',
                'value',
                'type',
                'is_private',
                'created_at',
                'updated_at',
            ])
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
