<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;

trait HasQueryFilters
{
    /**
     * Parse the `includes` query parameter safely whether passed as a string or array.
     *
     * @return array<int, string>
     */
    protected function parseRequestedIncludes(Request $request): array
    {
        $raw = $request->input(config('query-builder.parameters.include', 'includes'));

        if (is_string($raw)) {
            return array_values(array_unique(array_filter(
                array_map('trim', explode(',', $raw)),
                static fn (string $item): bool => $item !== '',
            )));
        }

        if (! is_array($raw)) {
            return [];
        }

        $parsed = [];
        foreach ($raw as $item) {
            if (! is_scalar($item)) {
                continue;
            }

            foreach (explode(',', (string) $item) as $part) {
                $trimmed = trim($part);
                if ($trimmed !== '') {
                    $parsed[] = $trimmed;
                }
            }
        }

        return array_values(array_unique($parsed));
    }

    protected function exactCaseInsensitiveFilter(string $name, ?string $internalName = null): AllowedFilter
    {
        return AllowedFilter::callback(
            $name,
            static function (Builder $query, mixed $value, string $property): void {
                if (! is_scalar($value)) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $normalized = mb_strtolower(trim((string) $value), 'UTF-8');

                if ($normalized === '') {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $column = $query->getQuery()->getGrammar()->wrap($query->qualifyColumn($property));
                $query->whereRaw("LOWER({$column}) = ?", [$normalized]);
            },
            $internalName,
        )->delimiter('');
    }
}
