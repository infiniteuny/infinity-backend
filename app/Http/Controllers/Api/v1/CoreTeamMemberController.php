<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoreTeamMember\StoreCoreTeamMemberRequest;
use App\Http\Requests\CoreTeamMember\UpdateCoreTeamMemberRequest;
use App\Models\CoreTeamMember;
use Illuminate\Http\Request;

class CoreTeamMemberController extends Controller
{
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
    public function store(StoreCoreTeamMemberRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CoreTeamMember $coreTeamMember)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCoreTeamMemberRequest $request, CoreTeamMember $coreTeamMember)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoreTeamMember $coreTeamMember)
    {
        //
    }
}
