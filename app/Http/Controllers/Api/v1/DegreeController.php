<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Degree\StoreDegreeRequest;
use App\Http\Requests\Degree\UpdateDegreeRequest;
use App\Models\Degree;
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
    public function index()
    {
        return Degree::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDegreeRequest $request)
    {
        $degree = Degree::create($request->validated());
        return response()->json(['message' => 'Degree berhasil ditambahkan', 'data' => $degree], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Degree $degree)
    {
        return $degree;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDegreeRequest $request, Degree $degree)
    {
        $degree->update($request->validated());
        return response()->json(['message' => 'Degree berhasil diubah', 'data' => $degree], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Degree $degree)
    {
        $degree->delete();
        return response()->json(['message' => 'Degree berhasil dihapus'], 200);
    }
}
