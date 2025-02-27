<?php

namespace App\Utils;

class ResponseFormatter
{
    /**
     * @param  string  $resourcesName  Resources name
     * @param  mixed  $data  Response data
     * @param  string  $message  Optional success message
     */
    public static function singleton(
        string $resourcesName,
        mixed $data,
        ?string $message = null,
    ): array {
        return JsendFormatter::success($message, [$resourcesName => $data]);
    }

    /**
     * @param  string  $resourcesName  Resources name
     * @param  mixed  $data  Response data
     * @param  int  $status  HTTP status code
     * @param  string  $message  Optional success message
     * @param  array  $extraHeaders  Optional extra headers
     */
    public static function collection(
        string $resourcesName,
        mixed $data,
        ?string $message = null,
    ): array {
        return JsendFormatter::success($message, [$resourcesName => $data]);
    }
}
