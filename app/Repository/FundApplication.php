<?php

namespace App\Repository;

use App\Http\Requests\FundApplication\StoreFundApplicationRequest;
use App\Http\Requests\FundApplication\UpdateFundApplicationRequest;
use App\Models\FundApplication;
use illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Utils\JsendResponseFormatter;
use Spatie\QueryBuilder\QueryBuilder;

class FundApplicationRepository
{
    public function index(Request $request): JsonResponse
    {
        $fundApplications = QueryBuilder::for(FundApplication::class)
            ->allowedFilters(['team_id', 'competition_id', 'competition_team_type_id', 'competition_scale_id', 'competition_branch', 'competition_date', 'letter_of_acceptance', 'proposal', 'status'])
            ->defaultSorts(['-created_at', 'id'])
            ->allowedSorts(['id', 'team_id', 'competition_id', 'competition_team_type_id', 'competition_scale_id', 'competition_branch', 'competition_date', 'letter_of_acceptance', 'proposal', 'status', 'created_at', 'updated_at'])
            ->paginate($request->query('per_page', 10));

        return JsendResponseFormatter::success_paginated('fundApplications', $fundApplications);
    }
    public function store(StoreFundApplicationRequest $request): JsonResponse
    {
        $fundApplication = FundApplication::create($request->validated());

        return JsendResponseFormatter::success_singleton('fundApplication', $fundApplication, 201);
    }
    public function show(FundApplication $fundApplication): JsonResponse
    {
        return JsendResponseFormatter::success_singleton('fundApplication', $fundApplication);
    }
    public function update(UpdateFundApplicationRequest $request, FundApplication $fundApplication): JsonResponse
    {
        $fundApplication->update($request->validated());

        return JsendResponseFormatter::success_singleton('fundApplication', $fundApplication);
    }
    public function destroy(FundApplication $fundApplication): JsonResponse
    {
        $fundApplication->delete();
        return JsendResponseFormatter::success_singleton('fundApplication', $fundApplication);
    }
}
