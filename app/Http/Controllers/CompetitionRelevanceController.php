<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompetitionRelevanceRequest;
use App\Http\Requests\UpdateCompetitionRelevanceRequest;
use App\Models\CompetitionRelevance;

class CompetitionRelevanceController extends Controller
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
    public function store(StoreCompetitionRelevanceRequest $request): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionRelevance $competitionRelevance): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompetitionRelevance $competitionRelevance): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionRelevanceRequest $request, CompetitionRelevance $competitionRelevance): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionRelevance $competitionRelevance): \Illuminate\Http\Response
    {
        //
    }
}
