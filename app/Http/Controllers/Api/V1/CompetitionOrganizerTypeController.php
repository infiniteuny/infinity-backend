<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionOrganizerType\StoreCompetitionOrganizerTypeRequest;
use App\Http\Requests\CompetitionOrganizerType\UpdateCompetitionOrganizerTypeRequest;
use App\Models\CompetitionOrganizerType;

class CompetitionOrganizerTypeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CompetitionOrganizerType::class, 'competitionOrganizerType');
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
    public function store(StoreCompetitionOrganizerTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionOrganizerType $competitionOrganizerType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionOrganizerTypeRequest $request, CompetitionOrganizerType $competitionOrganizerType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionOrganizerType $competitionOrganizerType)
    {
        //
    }
}
