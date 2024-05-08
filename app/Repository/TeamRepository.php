<?php

namespace App\Repository;

use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Models\Team;
use illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Utils\JsendResponseFormatter;
use Spatie\QueryBuilder\QueryBuilder;

class TeamRepository
{
    public function index(Request $request): JsonResponse
    {
        $teams = QueryBuilder::for(Team::class)
            ->allowedFilters(['code', 'name'])
            ->defaultSorts(['-created_at', 'id'])
            ->allowedSorts(['id', 'code', 'name', 'created_at', 'updated_at'])
            ->paginate($request->query('per_page', 10));

        return JsendResponseFormatter::success_paginated('teams', $teams);
    }
    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = Team::create($request->validated());

        return JsendResponseFormatter::success_singleton('team', $team, 201);
    }
    public function show(Team $team): JsonResponse
    {
        return JsendResponseFormatter::success_singleton('team', $team);
    }
    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $team->update($request->validated());

        return JsendResponseFormatter::success_singleton('team', $team);
    }
    public function destroy(Team $team): JsonResponse
    {
        $team->delete();
        return JsendResponseFormatter::success_singleton('team', $team);
    }
}
