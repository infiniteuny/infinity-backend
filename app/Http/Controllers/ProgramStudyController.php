<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudy;
use App\Http\Requests\StoreProgramStudyRequest;
use App\Http\Requests\UpdateProgramStudyRequest;

class ProgramStudyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreProgramStudyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProgramStudyRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProgramStudy  $programStudy
     * @return \Illuminate\Http\Response
     */
    public function show(ProgramStudy $programStudy)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProgramStudy  $programStudy
     * @return \Illuminate\Http\Response
     */
    public function edit(ProgramStudy $programStudy)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProgramStudyRequest  $request
     * @param  \App\Models\ProgramStudy  $programStudy
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProgramStudyRequest $request, ProgramStudy $programStudy)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProgramStudy  $programStudy
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProgramStudy $programStudy)
    {
        //
    }
}
