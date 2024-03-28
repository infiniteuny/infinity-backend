<?php

namespace App\Http\Controllers;

use App\Http\Requests\Freepik\StoreFreepikRequest;
use App\Http\Requests\Freepik\UpdateFreepikRequest;
use App\Models\Freepik;

class FreepikController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\Response
    {
        //
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
    public function store(StoreFreepikRequest $request): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Freepik $freepik): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Freepik $freepik): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFreepikRequest $request, Freepik $freepik): \Illuminate\Http\Response
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Freepik $freepik): \Illuminate\Http\Response
    {
        //
    }
}
