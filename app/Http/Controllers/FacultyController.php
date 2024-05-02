<?php

namespace App\Http\Controllers;

use App\Http\Requests\Faculty\StoreFacultyRequest;
use App\Http\Requests\Faculty\UpdateFacultyRequest;
use App\Models\Faculty;
use App\Models\ProgramStudy;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\Response
    {
        //
    }

    public function facultiesList(Request $request)
    {
        $faculties = Faculty::where('name', 'like', '%'.$request->input('q').'%')->get();
        $faculties = $faculties->map(function ($faculty) {
            return [
                'id' => $faculty->id,
                'name' => $faculty->name,
            ];
        });

        return response()->json($faculties);
    }

    public function programStudiesList(Request $request, $faculty)
    {
        $programStudies = ProgramStudy::where('faculty_id', $faculty)->where('name', 'like', '%'.$request->input('q').'%')->get();
        $programStudies = $programStudies->map(function ($programStudy) {
            return [
                'id' => $programStudy->id,
                'name' => $programStudy->grades->name.' - '.$programStudy->name,
            ];
        });

        return response()->json($programStudies);
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
    public function store(StoreFacultyRequest $request): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Faculty $faculty): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Faculty $faculty): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFacultyRequest $request, Faculty $faculty): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faculty $faculty): \Illuminate\Http\Response
    {
        //
    }
}
