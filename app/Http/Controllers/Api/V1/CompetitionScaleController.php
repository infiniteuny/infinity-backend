<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionScale\StoreCompetitionScaleRequest;
use App\Http\Requests\CompetitionScale\UpdateCompetitionScaleRequest;
use App\Models\CompetitionScale;

class CompetitionScaleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CompetitionScale::class, 'competitionScale');
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
    public function store(StoreCompetitionScaleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionScale $competitionScale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionScaleRequest $request, CompetitionScale $competitionScale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionScale $competitionScale)
    {
        //
    }
}
