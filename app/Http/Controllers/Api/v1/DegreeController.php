<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Degree\StoreDegreeRequest;
use App\Http\Requests\Degree\UpdateDegreeRequest;
use App\Models\Degree;
use Illuminate\Http\Request;

class DegreeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Degree::class, 'Degree');
    }
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
    public function store(StoreDegreeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Degree $degree)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDegreeRequest $request, Degree $degree)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Degree $degree)
    {
        //
    }
}
