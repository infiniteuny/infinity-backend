<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompetitionTimeRangeRequest;
use App\Http\Requests\UpdateCompetitionTimeRangeRequest;
use App\Models\CompetitionTimeRange;

class CompetitionTimeRangeController extends Controller
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
    public function store(StoreCompetitionTimeRangeRequest $request): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionTimeRange $competitionTimeRange): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionTimeRange $competitionTimeRange): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionTimeRangeRequest $request, CompetitionTimeRange $competitionTimeRange): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionTimeRange $competitionTimeRange): \Illuminate\Http\Response
    {
        //
    }
}
