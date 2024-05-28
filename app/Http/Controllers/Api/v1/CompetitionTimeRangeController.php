<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionTimeRange\StoreCompetitionTimeRangeRequest;
use App\Http\Requests\CompetitionTimeRange\UpdateCompetitionTimeRangeRequest;
use App\Models\CompetitionTimeRange;
use App\Repository\CompetitionTimeRangeRepository;
use Illuminate\Http\Request;

class CompetitionTimeRangeController extends Controller
{
    public function __construct(private CompetitionTimeRangeRepository $competitionTimeRangeRepository)
    {
        // $this->authorizeResource(CompetitionTimeRange::class, 'competitionTimeRange');
        $this->competitionTimeRangeRepository = $competitionTimeRangeRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, CompetitionTimeRangeRepository $competitionTimeRangeRepository)
    {
        return $this->competitionTimeRangeRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompetitionTimeRangeRequest $request)
    {
        return $this->competitionTimeRangeRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionTimeRange $competitionTimeRange)
    {
        return $this->competitionTimeRangeRepository->show($competitionTimeRange);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionTimeRangeRequest $request, CompetitionTimeRange $competitionTimeRange)
    {
        return $this->competitionTimeRangeRepository->update($request, $competitionTimeRange);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionTimeRange $competitionTimeRange)
    {
        return $this->competitionTimeRangeRepository->destroy($competitionTimeRange);
    }
}
