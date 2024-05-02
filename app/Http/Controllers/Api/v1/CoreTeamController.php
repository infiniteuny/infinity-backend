<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Models\CoreTeam;

class CoreTeamController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CoreTeam::class, 'coreTeam');
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
    public function store(StoreTeamRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CoreTeam $coreTeam)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, CoreTeam $coreTeam)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoreTeam $coreTeam)
    {
        //
    }
}
