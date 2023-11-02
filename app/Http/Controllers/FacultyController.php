<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacultyRequest;
use App\Http\Requests\UpdateFacultyRequest;
use App\Models\Faculty;
use App\Models\ProgramStudy;
use Illuminate\Http\Request;

class FacultyController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFacultyRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Faculty $faculty)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Faculty $faculty)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFacultyRequest $request, Faculty $faculty)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Faculty $faculty)
    {
        //
    }
}
