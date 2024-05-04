<?php

namespace App\Repository;

use App\Models\Degree;
use illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Utils\JsendResponseFormatter;
use Spatie\QueryBuilder\QueryBuilder;

class DegreeRepository
{
    // public function __construct(Degree $model)
    // {
    //     $this->authorizeResource(Degree::class, 'Degree');
    // }
    public function index(Request $request): JsonResponse
    {
        $degrees = QueryBuilder::for(Degree::class)
            ->allowedFilters(['code', 'name'])
            ->defaultSorts(['-created_at', 'id'])
            ->allowedSorts(['id', 'code', 'name', 'created_at', 'updated_at'])
            ->paginate($request->query('per_page', 10));

        return JsendResponseFormatter::success_paginated('degrees', $degrees);
    }
}
