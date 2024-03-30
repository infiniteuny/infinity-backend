<?php

// Taken from 'laravel-jsend' package

namespace App\Utils;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class JsendResponseFormatter
{
    /**
     * @param  string  $message Error message
     * @param  ?string  $code Optional custom error code
     * @param  string|array|null  $data Optional data
     * @param  int  $status HTTP status code
     */
    public static function error(string $message, ?string $code = null, string|array|null $data = null, int $status = 500, array $extraHeaders = []): ResponseFactory|Response
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];
        ! is_null($code) && $response['code'] = $code;
        ! is_null($data) && $response['data'] = $data;

        return response()->json($response, $status, $extraHeaders);
    }

    /**
     * @param  int  $status HTTP status code
     */
    public static function fail(array $data, int $status = 400, array $extraHeaders = []): ResponseFactory|Response
    {
        $response = [
            'status' => 'fail',
            'data' => $data,
        ];

        return response()->json($response, $status, $extraHeaders);
    }

    /**
     * @param  int  $status HTTP status code
     */
    public static function fail_validation(array $messages, int $status = 422, $extraHeaders = []): ResponseFactory|Response
    {
        return self::fail(['details' => $messages], $status, $extraHeaders);
    }

    /**
     * @param  int  $status HTTP status code
     */
    public static function success(array $data = [], int $status = 200, array $extraHeaders = []): ResponseFactory|Response
    {
        $response = [
            'status' => 'success',
            'data' => $data,
        ];

        return response()->json($response, $status, $extraHeaders);
    }

    /**
     * @param  int  $status HTTP status code
     */
    public static function success_singleton(string $resourcesName, $data, int $status = 200, $extraHeaders = []): ResponseFactory|Response
    {
        return self::success([$resourcesName => $data], $status, $extraHeaders);
    }

    /**
     * @param  int  $status HTTP status code
     */
    public static function success_paginated(string $resourcesName, LengthAwarePaginator $data, int $status = 200, $extraHeaders = []): ResponseFactory|Response
    {
        return self::success([
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
