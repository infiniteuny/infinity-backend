<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Major\StoreMajorRequest;
use App\Http\Requests\Major\UpdateMajorRequest;
use App\Models\Major;
use App\Repository\MajorRepository;
use Illuminate\Http\Request;

class MajorController extends Controller
{
    public function __construct(private MajorRepository $majorRepository)
    {
        // $this->authorizeResource(Major::class, 'major');
        $this->majorRepository = $majorRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->majorRepository->index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMajorRequest $request)
    {
        return $this->majorRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Major $major)
    {
        return $this->majorRepository->show($major);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMajorRequest $request, Major $major)
    {
        return $this->majorRepository->update($request, $major);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Major $major)
    {
        return $this->majorRepository->destroy($major);
    }
}
