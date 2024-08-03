<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeamMember\StoreTeamMemberRequest;
use App\Http\Requests\TeamMember\UpdateTeamMemberRequest;
use App\Models\TeamMember;

class TeamMemberController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(TeamMember::class, 'teamMember');
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
    public function store(StoreTeamMemberRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TeamMember $teamMember)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeamMember $teamMember)
    {
        //
    }
}
