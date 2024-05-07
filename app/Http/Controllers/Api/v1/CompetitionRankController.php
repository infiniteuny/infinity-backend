<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionRank\StoreCompetitionRankRequest;
use App\Http\Requests\CompetitionRank\UpdateCompetitionRankRequest;
use App\Models\CompetitionRank;

class CompetitionRankController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CompetitionRank::class, 'competitionRank');
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
    public function store(StoreCompetitionRankRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionRank $competitionRank)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionRankRequest $request, CompetitionRank $competitionRank)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionRank $competitionRank)
    {
        //
    }
}
