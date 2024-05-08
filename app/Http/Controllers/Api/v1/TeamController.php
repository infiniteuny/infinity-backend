<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Models\Team;
use App\Repository\TeamRepository;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct(private TeamRepository $teamRepository)
    {
        // $this->authorizeResource(Team::class, 'Team');
        $this->teamRepository = $teamRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->teamRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request)
    {
        return $this->teamRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        return $this->teamRepository->show($team);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        return $this->teamRepository->update($request, $team);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        return $this->teamRepository->destroy($team);
    }
}
