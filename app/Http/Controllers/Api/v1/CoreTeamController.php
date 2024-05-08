<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoreTeam\StoreCoreTeamRequest;
use App\Http\Requests\CoreTeam\UpdateCoreTeamRequest;
use App\Models\CoreTeam;
use App\Repository\CoreTeamRepository;
use Illuminate\Http\Request;

class CoreTeamController extends Controller
{
    public function __construct(private CoreTeamRepository $coreTeamRepository)
    {
        // $this->authorizeResource(CoreTeam::class, 'coreTeam');
        $this->coreTeamRepository = $coreTeamRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->coreTeamRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCoreTeamRequest $request)
    {
        return $this->coreTeamRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(CoreTeam $coreTeam)
    {
        return $this->coreTeamRepository->show($coreTeam);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoreTeamRequest $request, CoreTeam $coreTeam)
    {
        return $this->coreTeamRepository->update($request, $coreTeam);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoreTeam $coreTeam)
    {
        return $this->coreTeamRepository->destroy($coreTeam);
    }
}
