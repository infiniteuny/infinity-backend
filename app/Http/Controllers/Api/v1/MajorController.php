<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Major\StoreMajorRequest;
use App\Http\Requests\Major\UpdateMajorRequest;
use App\Models\Major;
use App\Repository\DegreeRepository;
use Illuminate\Http\Request;

class MajorController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Major::class, 'major');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Major::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMajorRequest $request)
    {
        $major = Major::create($request->validated());
        return response()->json(['message' => 'Major berhasil ditambahkan', 'data' => $major], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Major $major)
    {
        return $major;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMajorRequest $request, Major $major)
    {
        $major->update($request->validated());
        return response()->json(['message' => 'Major berhasil diubah', 'data' => $major], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Major $major)
    {
        $major->delete();
        return response()->json(['message' => 'Degree berhasil dihapus'], 200);
    }
}
