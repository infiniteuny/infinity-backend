<?php

namespace App\Repository;

use App\Http\Requests\CoreTeam\StoreCoreTeamRequest;
use App\Http\Requests\CoreTeam\UpdateCoreTeamRequest;
use App\Models\CoreTeam;
use illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Utils\JsendResponseFormatter;
use Spatie\QueryBuilder\QueryBuilder;

class CoreTeamRepository
{
    public function index(Request $request): JsonResponse
    {
        $coreTeams = QueryBuilder::for(CoreTeam::class)
            ->allowedFilters(['year'])
            ->defaultSorts(['-created_at', 'id'])
            ->allowedSorts(['id', 'year', 'created_at', 'updated_at'])
            ->paginate($request->query('per_page', 10));

        return JsendResponseFormatter::success_paginated('coreTeams', $coreTeams);
    }
    public function store(StoreCoreTeamRequest $request): JsonResponse
    {
        $coreTeam = CoreTeam::create($request->validated());

        return JsendResponseFormatter::success_singleton('coreTeam', $coreTeam, 201);
    }
    public function show(CoreTeam $coreTeam): JsonResponse
    {
        return JsendResponseFormatter::success_singleton('coreTeam', $coreTeam);
    }
    public function update(UpdateCoreTeamRequest $request, CoreTeam $coreTeam): JsonResponse
    {
        $coreTeam->update($request->validated());

        return JsendResponseFormatter::success_singleton('coreTeam', $coreTeam);
    }
    public function destroy(CoreTeam $coreTeam): JsonResponse
    {
        $coreTeam->delete();
        return JsendResponseFormatter::success_singleton('coreTeam', $coreTeam);
    }
}
