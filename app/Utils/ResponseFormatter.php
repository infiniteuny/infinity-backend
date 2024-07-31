<?php

namespace App\Utils;

use Illuminate\Pagination\LengthAwarePaginator;

class ResponseFormatter
{
    /**
     * @param  int  $status  HTTP status code
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function singleton(string $resourcesName, $data, int $status = 200, $extraHeaders = [])
    {
        return JsendFormatter::success([$resourcesName => $data], $status, $extraHeaders);
    }

    /**
     * @param  int  $status  HTTP status code
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function unpaginatedCollection(string $resourcesName, $data, int $status = 200, $extraHeaders = [])
    {
        return JsendFormatter::success([$resourcesName => $data], $status, $extraHeaders);
    }

    /**
     * @param  int  $status  HTTP status code
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function collection(string $resourcesName, LengthAwarePaginator $data, int $status = 200, $extraHeaders = [])
    {
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
