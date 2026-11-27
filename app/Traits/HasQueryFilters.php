<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;

trait HasQueryFilters
{
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
