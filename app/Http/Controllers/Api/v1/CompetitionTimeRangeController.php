<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionTimeRange\StoreCompetitionTimeRangeRequest;
use App\Http\Requests\CompetitionTimeRange\UpdateCompetitionTimeRangeRequest;
use App\Models\CompetitionTimeRange;
use Illuminate\Http\Request;

class CompetitionTimeRangeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CompetitionTimeRange::class, 'competitionTimeRange');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompetitionTimeRangeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionTimeRange $competitionTimeRange)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionTimeRangeRequest $request, CompetitionTimeRange $competitionTimeRange)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionTimeRange $competitionTimeRange)
    {
        //
    }
}
