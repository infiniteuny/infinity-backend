<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompetitionScaleRequest;
use App\Http\Requests\UpdateCompetitionScaleRequest;
use App\Models\CompetitionScale;

class CompetitionScaleController extends Controller
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
    public function store(StoreCompetitionScaleRequest $request): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionScale $competitionScale): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionScale $competitionScale): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionScaleRequest $request, CompetitionScale $competitionScale): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionScale $competitionScale): \Illuminate\Http\Response
    {
        //
    }
}
