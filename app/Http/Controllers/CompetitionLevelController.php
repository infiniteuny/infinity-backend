<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompetitionLevelRequest;
use App\Http\Requests\UpdateCompetitionLevelRequest;
use App\Models\CompetitionLevel;

class CompetitionLevelController extends Controller
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
    public function store(StoreCompetitionLevelRequest $request): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionLevel $competitionLevel): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionLevel $competitionLevel): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionLevelRequest $request, CompetitionLevel $competitionLevel): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionLevel $competitionLevel): \Illuminate\Http\Response
    {
        //
    }
}
