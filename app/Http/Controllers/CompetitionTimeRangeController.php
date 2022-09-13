<?php

namespace App\Http\Controllers;

use App\Models\CompetitionTimeRange;
use App\Http\Requests\StoreCompetitionTimeRangeRequest;
use App\Http\Requests\UpdateCompetitionTimeRangeRequest;

class CompetitionTimeRangeController extends Controller
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
     * @param  \App\Http\Requests\StoreCompetitionTimeRangeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompetitionTimeRangeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CompetitionTimeRange  $competitionTimeRange
     * @return \Illuminate\Http\Response
     */
    public function show(CompetitionTimeRange $competitionTimeRange)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CompetitionTimeRange  $competitionTimeRange
     * @return \Illuminate\Http\Response
     */
    public function edit(CompetitionTimeRange $competitionTimeRange)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCompetitionTimeRangeRequest  $request
     * @param  \App\Models\CompetitionTimeRange  $competitionTimeRange
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompetitionTimeRangeRequest $request, CompetitionTimeRange $competitionTimeRange)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CompetitionTimeRange  $competitionTimeRange
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompetitionTimeRange $competitionTimeRange)
    {
        //
    }
}
