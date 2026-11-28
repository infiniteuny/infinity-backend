<?php

namespace App\Http\Middleware;

use App\Utils\JsendFormatter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Pagination\Cursor;
use Symfony\Component\HttpFoundation\Response;

class SanitizeApiQuery
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->containsNullByte($request->query->all())) {
            return response()->json(
                JsendFormatter::fail('Invalid query parameter value.'),
                400,
            );
        }

        $filters = $request->query('filters');
        if (is_array($filters)) {
            foreach ($filters as $filterValue) {
                if (is_array($filterValue)) {
                    foreach ($filterValue as $nestedValue) {
                        if (is_array($nestedValue)) {
                            return response()->json(
                                JsendFormatter::fail('Invalid query parameter value.'),
                                400,
                            );
                        }
                    }
                }
            }
        }

        if ($request->isMethodSafe() && $request->query->has('cursor')) {
            $rawCursor = $request->query('cursor');
            if ($rawCursor !== null && $rawCursor !== '') {
                if (! is_string($rawCursor)) {
                    return response()->json(
                        JsendFormatter::fail('Invalid pagination cursor.'),
                        400,
                    );
                }

                $decodedCursor = Cursor::fromEncoded($rawCursor);
                if ($decodedCursor === null) {
                    return response()->json(
                        JsendFormatter::fail('Invalid pagination cursor.'),
                        400,
                    );
                }

                foreach ($decodedCursor->toArray() as $cursorKey => $cursorValue) {
                    if ($cursorKey === '_pointsToNextItems') {
                        if (! is_bool($cursorValue)) {
                            return response()->json(
                                JsendFormatter::fail('Invalid pagination cursor.'),
                                400,
                            );
                        }

                        continue;
                    }

                    if (! is_scalar($cursorValue) && $cursorValue !== null) {
                        return response()->json(
                            JsendFormatter::fail('Invalid pagination cursor.'),
                            400,
                        );
                    }
                }
            }
        }

        return $next($request);
    }

    private function containsNullByte(mixed $value): bool
    {
        if (is_string($value)) {
            return str_contains($value, "\0");
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                if ((is_string($key) && str_contains($key, "\0")) || $this->containsNullByte($item)) {
                    return true;
                }
            }
        }

        return false;
    }
}
