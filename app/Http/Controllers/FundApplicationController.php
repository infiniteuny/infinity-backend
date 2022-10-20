<?php

namespace App\Http\Controllers;

use App\Models\FundApplication;
use App\Http\Requests\StoreFundApplicationRequest;
use App\Http\Requests\UpdateFundApplicationRequest;

class FundApplicationController extends Controller
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
     * @param  \App\Http\Requests\StoreFundApplicationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFundApplicationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FundApplication  $fundApplication
     * @return \Illuminate\Http\Response
     */
    public function show(FundApplication $fundApplication)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FundApplication  $fundApplication
     * @return \Illuminate\Http\Response
     */
    public function edit(FundApplication $fundApplication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFundApplicationRequest  $request
     * @param  \App\Models\FundApplication  $fundApplication
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFundApplicationRequest $request, FundApplication $fundApplication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FundApplication  $fundApplication
     * @return \Illuminate\Http\Response
     */
    public function destroy(FundApplication $fundApplication)
    {
        //
    }
}
