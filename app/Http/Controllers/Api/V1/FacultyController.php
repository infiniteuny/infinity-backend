<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faculty\StoreFacultyRequest;
use App\Http\Requests\Faculty\UpdateFacultyRequest;
use App\Models\Faculty;
use App\Repository\FacultyRepository;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function __construct(private FacultyRepository $facultyRepository)
    {
        // $this->authorizeResource(Faculty::class, 'faculty');
        $this->facultyRepository = $facultyRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, FacultyRepository $facultyRepository)
    {
        return $this->facultyRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFacultyRequest $request)
    {
        return $this->facultyRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Faculty $faculty)
    {
        return $this->facultyRepository->show($faculty);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFacultyRequest $request, Faculty $faculty)
    {
        return $this->facultyRepository->update($request, $faculty);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faculty $faculty)
    {
        return $this->facultyRepository->destroy($faculty);
    }
}
