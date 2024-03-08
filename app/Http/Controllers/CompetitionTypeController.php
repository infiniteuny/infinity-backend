<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompetitionTypeRequest;
use App\Http\Requests\UpdateCompetitionTypeRequest;
use App\Models\CompetitionType;

class CompetitionTypeController extends Controller
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
    public function store(StoreCompetitionTypeRequest $request): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionType $competitionType): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionType $competitionType): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionTypeRequest $request, CompetitionType $competitionType): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionType $competitionType): \Illuminate\Http\Response
    {
        //
    }
}
