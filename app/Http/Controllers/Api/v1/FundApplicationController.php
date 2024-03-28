<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FundApplication\StoreFundApplicationRequest;
use App\Http\Requests\FundApplication\UpdateFundApplicationRequest;
use App\Models\FundApplication;
use Illuminate\Http\Request;

class FundApplicationController extends Controller
{
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
    public function store(StoreFundApplicationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FundApplication $fundApplication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFundApplicationRequest $request, FundApplication $fundApplication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FundApplication $fundApplication)
    {
        //
    }
}
