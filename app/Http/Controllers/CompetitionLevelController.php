<?php

namespace App\Http\Controllers;

use App\Models\CompetitionLevel;
use App\Http\Requests\StoreCompetitionLevelRequest;
use App\Http\Requests\UpdateCompetitionLevelRequest;

class CompetitionLevelController extends Controller
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
     * @param  \App\Http\Requests\StoreCompetitionLevelRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompetitionLevelRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CompetitionLevel  $competitionLevel
     * @return \Illuminate\Http\Response
     */
    public function show(CompetitionLevel $competitionLevel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CompetitionLevel  $competitionLevel
     * @return \Illuminate\Http\Response
     */
    public function edit(CompetitionLevel $competitionLevel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCompetitionLevelRequest  $request
     * @param  \App\Models\CompetitionLevel  $competitionLevel
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompetitionLevelRequest $request, CompetitionLevel $competitionLevel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CompetitionLevel  $competitionLevel
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompetitionLevel $competitionLevel)
    {
        //
    }
}
