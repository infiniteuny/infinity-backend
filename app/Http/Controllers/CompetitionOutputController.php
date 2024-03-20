<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompetitionOutput\StoreCompetitionOutputRequest;
use App\Http\Requests\CompetitionOutput\UpdateCompetitionOutputRequest;

use App\Models\CompetitionOutput;

class CompetitionOutputController extends Controller
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
    public function store(StoreCompetitionOutputRequest $request): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionOutput $competitionOutput): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionOutput $competitionOutput): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionOutputRequest $request, CompetitionOutput $competitionOutput): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionOutput $competitionOutput): \Illuminate\Http\Response
    {
        //
    }
}
