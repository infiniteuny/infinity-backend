<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoreTeamDivision\StoreCoreTeamDivisionRequest;
use App\Http\Requests\CoreTeamDivision\UpdateCoreTeamDivisionRequest;
use App\Models\CoreTeamDivision;

class CoreTeamDivisionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CoreTeamDivision::class, 'coreTeamDivision');
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
    public function store(StoreCoreTeamDivisionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CoreTeamDivision $coreTeamDivision)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoreTeamDivisionRequest $request, CoreTeamDivision $coreTeamDivision)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoreTeamDivision $coreTeamDivision)
    {
        //
    }
}
