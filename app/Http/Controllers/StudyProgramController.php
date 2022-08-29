<?php

namespace App\Http\Controllers;

use App\Models\StudyProgram;
use App\Http\Requests\StoreStudyProgramRequest;
use App\Http\Requests\UpdateStudyProgramRequest;

class StudyProgramController extends Controller
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
     * @param  \App\Http\Requests\StoreStudyProgramRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStudyProgramRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StudyProgram  $studyProgram
     * @return \Illuminate\Http\Response
     */
    public function show(StudyProgram $studyProgram)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StudyProgram  $studyProgram
     * @return \Illuminate\Http\Response
     */
    public function edit(StudyProgram $studyProgram)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStudyProgramRequest  $request
     * @param  \App\Models\StudyProgram  $studyProgram
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStudyProgramRequest $request, StudyProgram $studyProgram)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StudyProgram  $studyProgram
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudyProgram $studyProgram)
    {
        //
    }
}
