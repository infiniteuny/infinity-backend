<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompetitionRankRequest;
use App\Http\Requests\UpdateCompetitionRankRequest;
use App\Models\CompetitionRank;

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
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompetitionRankRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(CompetitionRank $competitionRank)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(CompetitionRank $competitionRank)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompetitionRankRequest $request, CompetitionRank $competitionRank)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompetitionRank $competitionRank)
    {
        //
    }
}
