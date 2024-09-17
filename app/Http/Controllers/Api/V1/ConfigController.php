<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\StoreConfigRequest;
use App\Http\Requests\Config\UpdateConfigRequest;
use App\Models\Config;
use App\Utils\ResponseFormatter;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;

class ConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create,'.Config::class)->only('store');
        $this->middleware('can:update,'.Config::class)->only('update');
        $this->middleware('can:delete,'.Config::class)->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $configs = QueryBuilder::for(Config::class)
            ->defaultSorts([
                '-created_at',
                'id',
            ]);

        if (Auth::check()) {
            $configs = $configs
                ->allowedFilters([
                    'key',
                    'type',
                    'is_private',
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
                    'type',
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
            ->paginate($request->query('per_page', 10));

        return ResponseFormatter::paginatedCollection('configs', $configs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConfigRequest $request)
    {
        $config = Config::create($request->validated());

        return ResponseFormatter::singleton('survey_result', $config, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Config $config)
    {
        if ($config->is_private && ! Auth::check()) {
            throw new AuthenticationException;
        }

        return ResponseFormatter::singleton('survey_result', $config);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConfigRequest $request, Config $config)
    {
        $config->update($request->validated());

        return ResponseFormatter::singleton('config', $config);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Config $config)
    {
        $config->delete();

        return ResponseFormatter::singleton('config', $config);
    }
}
