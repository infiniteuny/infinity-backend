<?php

namespace App\Http\Controllers;

use App\Models\CompetitionRank;
use App\Http\Requests\StoreCompetitionRankRequest;
use App\Http\Requests\UpdateCompetitionRankRequest;

class CompetitionRankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCompetitionRankRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompetitionRankRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CompetitionRank  $competitionRank
     * @return \Illuminate\Http\Response
     */
    public function show(CompetitionRank $competitionRank)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CompetitionRank  $competitionRank
     * @return \Illuminate\Http\Response
     */
    public function edit(CompetitionRank $competitionRank)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCompetitionRankRequest  $request
     * @param  \App\Models\CompetitionRank  $competitionRank
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompetitionRankRequest $request, CompetitionRank $competitionRank)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CompetitionRank  $competitionRank
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompetitionRank $competitionRank)
    {
        //
    }
}
