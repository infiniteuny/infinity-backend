<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionOutput\StoreCompetitionOutputRequest;
use App\Http\Requests\CompetitionOutput\UpdateCompetitionOutputRequest;
use App\Models\CompetitionOutput;

class CompetitionOutputController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CompetitionOutput::class, 'competitionOutput');
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
    public function store(StoreCompetitionOutputRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionOutput $competitionOutput)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionOutputRequest $request, CompetitionOutput $competitionOutput)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionOutput $competitionOutput)
    {
        //
    }
}
