<?php

namespace App\Utils;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * @mixin \Illuminate\Foundation\Exceptions\Handler
 */
trait JsendExceptionFormatter
{
    /**
     * Convert a validation exception into a JSON response.
     */
    protected function invalidJson($request, ValidationException $exception): ResponseFactory|Response
    {
        return JsendResponseFormatter::fail_validation(
            $exception->errors(),
            $exception->status
        );
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param  \Exception  $e
     */
    protected function prepareJsonResponse($request, Throwable $e): ResponseFactory|Response
    {
        $message = 'Internal server error.';
        $code = $e->getCode() ?: null;
        $data = config('app.debug') ? [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : null;

        if (config('app.debug') || $this->isHttpException($e)) {
            $message = $e->getMessage();
        }

        return JsendResponseFormatter::error(
            $message,
            $code,
            $data,
            ($e instanceof HttpExceptionInterface) ? $e->getStatusCode() : 500,
            ($e instanceof HttpExceptionInterface) ? $e->getHeaders() : [],
        );
    }
}
