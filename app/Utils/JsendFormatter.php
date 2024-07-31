<?php

// Taken from 'laravel-jsend' package

namespace App\Utils;

class JsendFormatter
{
    /**
     * @param  string  $message  Error message
     * @param  string  $code  Optional custom error code
     * @param  array  $data  Optional data
     * @param  int  $status  HTTP status code
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function error(string $message, ?string $code = null, $data = [], int $status = 500, array $extraHeaders = [])
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];
        ! is_null($code) && $response['code'] = $code;
        $response['data'] = array_merge(['message' => $message], $data);

        return response()->json($response, $status, $extraHeaders);
    }

    /**
     * @param  int  $status  HTTP status code
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function fail(array $data, int $status = 400, array $extraHeaders = [])
    {
        $response = [
            'status' => 'fail',
            'data' => $data,
        ];

        return response()->json($response, $status, $extraHeaders);
    }

    /**
     * @param  array  $data
     * @param  int  $status  HTTP status code
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function success($data = [], int $status = 200, array $extraHeaders = [])
    {
        $response = [
            'status' => 'success',
            'data' => $data,
        ];

        return response()->json($response, $status, $extraHeaders);
    }
}
