<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FundApplication\StoreFundApplicationRequest;
use App\Http\Requests\FundApplication\UpdateFundApplicationRequest;
use App\Models\FundApplication;
use App\Repository\FundApplicationRepository;
use Illuminate\Http\Request;

class FundApplicationController extends Controller
{
    public function __construct(private FundApplicationRepository $fundApplicationRepository)
    {
        // $this->authorizeResource(FundApplication::class, 'fundApplication');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->fundApplicationRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFundApplicationRequest $request)
    {
        return $this->fundApplicationRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(FundApplication $fundApplication)
    {
        return $this->fundApplicationRepository->show($fundApplication);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFundApplicationRequest $request, FundApplication $fundApplication)
    {
        return $this->fundApplicationRepository->update($request, $fundApplication);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FundApplication $fundApplication)
    {
        return $this->fundApplicationRepository->destroy($fundApplication);
    }
}
