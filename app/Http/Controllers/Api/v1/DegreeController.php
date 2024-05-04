<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Degree\StoreDegreeRequest;
use App\Http\Requests\Degree\UpdateDegreeRequest;
use App\Models\Degree;
use App\Repository\DegreeRepository;
use Illuminate\Http\Request;

class DegreeController extends Controller
{
    // public function __construct()
    // {
    //     $this->authorizeResource(Degree::class, 'Degree');
    // }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DegreeRepository $degreeRepository)
    {
        return Degree::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDegreeRequest $request, DegreeRepository $degreeRepository)
    {
        $degree = Degree::create($request->validated());
        return response()->json(['message' => 'Degree berhasil ditambahkan', 'data' => $degree], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DegreeRepository $degreeRepository, Degree $degree)
    {
        return $degree;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDegreeRequest $request, Degree $degree, DegreeRepository $degreeRepository)
    {
        $degree->update($request->validated());
        return response()->json(['message' => 'Degree berhasil diubah', 'data' => $degree], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Degree $degree, DegreeRepository $degreeRepository)
    {
        $degree->delete();
        return response()->json(['message' => 'Degree berhasil dihapus'], 200);
    }
}
