<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompetitionRankRequest;
use App\Http\Requests\UpdateCompetitionRankRequest;
use App\Models\CompetitionRank;

class CompetitionRankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompetitionRankRequest $request): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionRank $competitionRank): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionRank $competitionRank): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionRankRequest $request, CompetitionRank $competitionRank): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionRank $competitionRank): \Illuminate\Http\Response
    {
        //
    }
}
