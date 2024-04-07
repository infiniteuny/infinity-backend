<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\StoreConfigRequest;
use App\Http\Requests\Config\UpdateConfigRequest;
use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Config::class, 'Config');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConfigRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Config $config)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConfigRequest $request, Config $config)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Config $config)
    {
        //
    }
}
