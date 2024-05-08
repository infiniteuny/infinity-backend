<?php

namespace App\Repository;

use App\Http\Requests\Major\StoreMajorRequest;
use App\Http\Requests\Major\UpdateMajorRequest;
use App\Models\Major;
use illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Utils\JsendResponseFormatter;
use Spatie\QueryBuilder\QueryBuilder;

class MajorRepository
{
    public function index(Request $request): JsonResponse
    {
        $majors = QueryBuilder::for(Major::class)
            ->allowedFilters(['code', 'name', 'degree_id', 'faculty_id'])
            ->defaultSorts(['-created_at', 'id'])
            ->allowedSorts(['id', 'code', 'name', 'degree_id', 'faculty_id', 'created_at', 'updated_at'])
            ->paginate($request->query('per_page', 10));

        return JsendResponseFormatter::success_paginated('majors', $majors);
    }
    public function store(StoreMajorRequest $request): JsonResponse
    {
        $major = Major::create($request->validated());

        return JsendResponseFormatter::success_singleton('major', $major, 201);
    }
    public function show(Major $major): JsonResponse
    {
        return JsendResponseFormatter::success_singleton('major', $major);
    }
    public function update(UpdateMajorRequest $request, Major $major): JsonResponse
    {
        $major->update($request->validated());

        return JsendResponseFormatter::success_singleton('major', $major);
    }
    public function destroy(Major $major): JsonResponse
    {
        $major->delete();
        return JsendResponseFormatter::success_singleton('major', $major);
    }
}
