<?php

// Taken from 'laravel-jsend' package

namespace App\Utils;

use Illuminate\Http\JsonResponse;

class JsendFormatter
{
    /**
     * @param  string  $message  Error message
     * @param  string|null  $code  Optional custom error code
     * @param  array|null  $data  Optional response data
     * @param  int  $status  HTTP status code
     */
    public static function error(string $message, ?string $code = null, ?array $data = [], int $status = 500, array $extraHeaders = []): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];
        ! is_null($code) && $response['code'] = $code;
        $response['data'] = array_merge(['message' => $message], $data ?? []);

        return response()->json($response, $status, $extraHeaders);
    }

    /**
     * @param  array|null  $data  Response data
     * @param  int  $status  HTTP status code
     * @param  array  $extraHeaders  Optional extra headers
     */
    public static function fail(?array $data, int $status = 400, array $extraHeaders = []): JsonResponse
    {
        $response = [
            'status' => 'fail',
            'data' => $data,
        ];

        return response()->json($response, $status, $extraHeaders);
    }

    /**
     * @param  array|null  $data  Response data
     * @param  int  $status  HTTP status code
     * @param  array  $extraHeaders  Optional extra headers
     */
    public static function success(?array $data = [], int $status = 200, array $extraHeaders = []): JsonResponse
    {
        $response = [
            'status' => 'success',
            'data' => $data,
        ];

        return response()->json($response, $status, $extraHeaders);
    }
}
