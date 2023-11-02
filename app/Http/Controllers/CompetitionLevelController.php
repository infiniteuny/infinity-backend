<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompetitionLevelRequest;
use App\Http\Requests\UpdateCompetitionLevelRequest;
use App\Models\CompetitionLevel;

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
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompetitionLevelRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(CompetitionLevel $competitionLevel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(CompetitionLevel $competitionLevel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompetitionLevelRequest $request, CompetitionLevel $competitionLevel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompetitionLevel $competitionLevel)
    {
        //
    }
}
