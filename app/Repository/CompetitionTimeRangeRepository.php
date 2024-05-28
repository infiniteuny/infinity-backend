<?php

namespace App\Repository;

use App\Http\Requests\CompetitionTimeRange\StoreCompetitionTimeRangeRequest;
use App\Http\Requests\CompetitionTimeRange\UpdateCompetitionTimeRangeRequest;
use App\Models\CompetitionTimeRange;
use illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Utils\JsendResponseFormatter;
use Spatie\QueryBuilder\QueryBuilder;

class CompetitionTimeRangeRepository
{
    public function index(Request $request): JsonResponse
    {
        $competitionTimeRanges = QueryBuilder::for(CompetitionTimeRange::class)
            ->allowedFilters(['name', 'weight'])
            ->defaultSorts(['-created_at', 'id'])
            ->allowedSorts(['id', 'name', 'weight', 'created_at', 'updated_at'])
            ->paginate($request->query('per_page', 10));

        return JsendResponseFormatter::success_paginated('competitionTimeRanges', $competitionTimeRanges);
    }
    public function store(StoreCompetitionTimeRangeRequest $request): JsonResponse
    {
        $competitionTimeRange = CompetitionTimeRange::create($request->validated());

        return JsendResponseFormatter::success_singleton('competitionTimeRange', $competitionTimeRange, 201);
    }
    public function show(CompetitionTimeRange $competitionTimeRange): JsonResponse
    {
        return JsendResponseFormatter::success_singleton('competitionTimeRange', $competitionTimeRange);
    }
    public function update(UpdateCompetitionTimeRangeRequest $request, CompetitionTimeRange $competitionTimeRange): JsonResponse
    {
        $competitionTimeRange->update($request->validated());

        return JsendResponseFormatter::success_singleton('competitionTimeRange', $competitionTimeRange);
    }
    public function destroy(CompetitionTimeRange $competitionTimeRange): JsonResponse
    {
        $competitionTimeRange->delete();
        return JsendResponseFormatter::success_singleton('competitionTimeRange', $competitionTimeRange);
    }
}
