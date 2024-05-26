<?php

namespace App\Repository;

use App\Http\Requests\CompetitionOutput\StoreCompetitionOutputRequest;
use App\Http\Requests\CompetitionOutput\UpdateCompetitionOutputRequest;
use App\Models\CompetitionOutput;
use illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Utils\JsendResponseFormatter;
use Spatie\QueryBuilder\QueryBuilder;

class CompetitionOutputRepository
{
    public function index(Request $request): JsonResponse
    {
        $competitionOutputs = QueryBuilder::for(CompetitionOutput::class)
            ->allowedFilters(['code', 'name'])
            ->defaultSorts(['-created_at', 'id'])
            ->allowedSorts(['id', 'code', 'name', 'created_at', 'updated_at'])
            ->paginate($request->query('per_page', 10));

        return JsendResponseFormatter::success_paginated('competitionOutputs', $competitionOutputs);
    }
    public function store(StoreCompetitionOutputRequest $request): JsonResponse
    {
        $competitionOutput = CompetitionOutput::create($request->validated());

        return JsendResponseFormatter::success_singleton('competitionOutput', $competitionOutput, 201);
    }
    public function show(CompetitionOutput $competitionOutput): JsonResponse
    {
        return JsendResponseFormatter::success_singleton('competitionOutput', $competitionOutput);
    }
    public function update(UpdateCompetitionOutputRequest $request, CompetitionOutput $competitionOutput): JsonResponse
    {
        $competitionOutput->update($request->validated());

        return JsendResponseFormatter::success_singleton('competitionOutput', $competitionOutput);
    }
    public function destroy(CompetitionOutput $competitionOutput): JsonResponse
    {
        $competitionOutput->delete();
        return JsendResponseFormatter::success_singleton('competitionOutput', $competitionOutput);
    }
}
