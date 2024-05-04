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
    public function __construct(private DegreeRepository $degreeRepository)
    {
        // $this->authorizeResource(Degree::class, 'Degree');
        $this->degreeRepository = $degreeRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DegreeRepository $degreeRepository)
    {
        return $degreeRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDegreeRequest $request)
    {
        return $this->degreeRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Degree $degree)
    {
        return $this->degreeRepository->show($degree);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDegreeRequest $request, Degree $degree)
    {
        return $this->degreeRepository->update($request, $degree);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Degree $degree)
    {
        return $this->degreeRepository->destroy($degree);
    }
}
