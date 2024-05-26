<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionOutput\StoreCompetitionOutputRequest;
use App\Http\Requests\CompetitionOutput\UpdateCompetitionOutputRequest;
use App\Models\CompetitionOutput;
use App\Repository\CompetitionOutputRepository;
use Illuminate\Http\Request;

class CompetitionOutputController extends Controller
{
    public function __construct(private CompetitionOutputRepository $competitionOutputRepository)
    {
        // $this->authorizeResource(CompetitionOutput::class, 'competitionOutput');
        $this->competitionOutputRepository = $competitionOutputRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, CompetitionOutputRepository $competitionOutputRepository)
    {
        return $this->competitionOutputRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompetitionOutputRequest $request)
    {
        return $this->competitionOutputRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(CompetitionOutput $competitionOutput)
    {
        return $this->competitionOutputRepository->show($competitionOutput);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompetitionOutputRequest $request, CompetitionOutput $competitionOutput)
    {
        return $this->competitionOutputRepository->update($request, $competitionOutput);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompetitionOutput $competitionOutput)
    {
        return $this->competitionOutputRepository->destroy($competitionOutput);
    }
}
