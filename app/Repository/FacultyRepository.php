<?php

namespace App\Repository;

use App\Http\Requests\Faculty\StoreFacultyRequest;
use App\Http\Requests\Faculty\UpdateFacultyRequest;
use App\Models\Faculty;
use illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Utils\JsendResponseFormatter;
use Spatie\QueryBuilder\QueryBuilder;

class FacultyRepository
{
    public function index(Request $request): JsonResponse
    {
        $faculties = QueryBuilder::for(Faculty::class)
            ->allowedFilters(['code', 'name'])
            ->defaultSorts(['-created_at', 'id'])
            ->allowedSorts(['id', 'code', 'name', 'created_at', 'updated_at'])
            ->paginate($request->query('per_page', 10));

        return JsendResponseFormatter::success_paginated('faculties', $faculties);
    }
    public function store(StoreFacultyRequest $request): JsonResponse
    {
        $faculty = Faculty::create($request->validated());

        return JsendResponseFormatter::success_singleton('faculty', $faculty, 201);
    }
    public function show(Faculty $faculty): JsonResponse
    {
        return JsendResponseFormatter::success_singleton('faculty', $faculty);
    }
    public function update(UpdateFacultyRequest $request, Faculty $faculty): JsonResponse
    {
        $faculty->update($request->validated());

        return JsendResponseFormatter::success_singleton('faculty', $faculty);
    }
    public function destroy(Faculty $faculty): JsonResponse
    {
        $faculty->delete();
        return JsendResponseFormatter::success_singleton('faculty', $faculty);
    }
}
