<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionTeamType\StoreCompetitionTeamTypeRequest;
use App\Http\Requests\CompetitionTeamType\UpdateCompetitionTeamTypeRequest;
use App\Models\CompetitionTeamType;
use Illuminate\Http\Request;

class CompetitionTeamTypeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CompetitionTeamType::class, 'competitionTeamType');
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
    public function store(StoreCompetitionTeamTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionTeamType $competitionTeamType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionTeamTypeRequest $request, CompetitionTeamType $competitionTeamType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionTeamType $competitionTeamType)
    {
        //
    }
}
