<?php

namespace App\Http\Controllers;

use App\Models\CompetitionType;
use App\Http\Requests\StoreCompetitionTypeRequest;
use App\Http\Requests\UpdateCompetitionTypeRequest;

class CompetitionTypeController extends Controller
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
     * @param  \App\Http\Requests\StoreCompetitionTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompetitionTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CompetitionType  $competitionType
     * @return \Illuminate\Http\Response
     */
    public function show(CompetitionType $competitionType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CompetitionType  $competitionType
     * @return \Illuminate\Http\Response
     */
    public function edit(CompetitionType $competitionType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCompetitionTypeRequest  $request
     * @param  \App\Models\CompetitionType  $competitionType
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompetitionTypeRequest $request, CompetitionType $competitionType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CompetitionType  $competitionType
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompetitionType $competitionType)
    {
        //
    }
}
