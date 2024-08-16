<?php

namespace App\Utils;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ResponseFormatter
{
    /**
     * @param  string  $resourcesName  Resources name
     * @param  mixed  $data  Response data
     * @param  int  $status  HTTP status code
     * @param  array  $extraHeaders  Optional extra headers
     */
    public static function singleton(
        string $resourcesName,
        mixed $data,
        int $status = 200,
        array $extraHeaders = []
    ): JsonResponse {
        return JsendFormatter::success([$resourcesName => $data], $status, $extraHeaders);
    }

    /**
     * @param  string  $resourcesName  Resources name
     * @param  mixed  $data  Response data
     * @param  int  $status  HTTP status code
     * @param  array  $extraHeaders  Optional extra headers
     */
    public static function collection(
        string $resourcesName,
        mixed $data,
        int $status = 200,
        array $extraHeaders = []
    ): JsonResponse {
        return JsendFormatter::success([$resourcesName => $data], $status, $extraHeaders);
    }

    /**
     * @param  string  $resourcesName  Resources name
     * @param  LengthAwarePaginator  $data  Paginated response data
     * @param  int  $status  HTTP status code
     * @param  array  $extraHeaders  Optional extra headers
     */
    public static function paginatedCollection(
        string $resourcesName,
        LengthAwarePaginator $data,
        int $status = 200,
        array $extraHeaders = []
    ): JsonResponse {
        return JsendFormatter::success([
            $resourcesName => $data->items(),
            'meta' => [
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ],
        ], $status, $extraHeaders);
    }
}
