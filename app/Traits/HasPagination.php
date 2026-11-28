<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HasPagination
{
    protected function resolvePerPage(Request $request, int $default = 10, int $max = 500): int
    {
        $raw = $request->query('per_page');

        if (! is_scalar($raw) || trim((string) $raw) === '') {
            return $default;
        }

        $parsed = filter_var($raw, FILTER_VALIDATE_INT);

        if ($parsed === false) {
            return $default;
        }

        return max(1, min($max, $parsed));
    }
}
