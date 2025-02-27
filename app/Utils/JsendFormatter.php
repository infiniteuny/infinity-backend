<?php

// Taken from 'laravel-jsend' package

// This formatter adheres to the JSend specification (https://github.com/omniti-labs/jsend)
// with additional message field for fail and success responses.

namespace App\Utils;

class JsendFormatter
{
    /**
     * @param  string  $message  Error message
     * @param  string|null  $code  Optional custom error code
     * @param  array|null  $data  Optional response data
     */
    public static function error(string $message, ?string $code = null, ?array $data = null): array
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];
        ! is_null($code) && $response['code'] = $code;
        ! is_null($data) && $response['data'] = $data;

        return $response;
    }

    /**
     * @param  string|null  $message  Fail message
     * @param  array|null  $data  Response data
     */
    public static function fail(?string $message, ?array $data = null): array
    {
        $response['status'] = 'fail';
        ! is_null($message) && $response['message'] = $message;
        $response['data'] = $data;

        return $response;
    }

    /**
     * @param  string|null  $message  Success message
     * @param  array|null  $data  Response data
     */
    public static function success(?string $message = null, ?array $data = null): array
    {
        $response['status'] = 'success';
        ! is_null($message) && $response['message'] = $message;
        $response['data'] = $data;

        return $response;
    }
}
