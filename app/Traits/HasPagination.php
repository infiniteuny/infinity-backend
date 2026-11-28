<?php

namespace App\Traits;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;
use UnexpectedValueException;

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

    /**
     * @param  array<int, string>  $uniqueColumns
     * @param  array<int, string>  $requiredForAppends
     */
    protected function cursorPaginateWithTieBreaker(
        QueryBuilder|EloquentBuilder|Relation $query,
        Request $request,
        array $uniqueColumns = ['id'],
        array $requiredForAppends = [],
    ): CursorPaginator {
        $cursor = null;
        if ($request->query->has('cursor')) {
            $rawCursor = $request->query('cursor');
            if ($rawCursor !== null && $rawCursor !== '') {
                if (! is_string($rawCursor)) {
                    throw new UnexpectedValueException('Invalid pagination cursor.');
                }

                $cursor = Cursor::fromEncoded($rawCursor);
                if ($cursor === null) {
                    throw new UnexpectedValueException('Invalid pagination cursor.');
                }

                foreach ($cursor->toArray() as $cursorKey => $cursorValue) {
                    if ($cursorKey === '_pointsToNextItems') {
                        if (! is_bool($cursorValue)) {
                            throw new UnexpectedValueException('Invalid pagination cursor.');
                        }

                        continue;
                    }

                    if (! is_scalar($cursorValue) && $cursorValue !== null) {
                        throw new UnexpectedValueException('Invalid pagination cursor.');
                    }
                }
            }
        }

        $eloquent = match (true) {
            $query instanceof QueryBuilder => $query->getEloquentBuilder(),
            $query instanceof Relation => $query->getQuery(),
            default => $query,
        };
        $baseQuery = $eloquent->getQuery();
        $model = $eloquent->getModel();
        $table = $model->getTable();
        $keyName = $model->getKeyName();
        $qualifiedKeyName = $model->qualifyColumn($keyName);

        $hasRawDistinct = is_array($baseQuery->columns) && collect($baseQuery->columns)->contains(
            static fn (mixed $col): bool => $col instanceof Expression
                && preg_match('/^\s*distinct\b/i', (string) $col->getValue($baseQuery->getGrammar())) === 1,
        );
        $isDistinct = (bool) $baseQuery->distinct || $hasRawDistinct;

        if (! $isDistinct) {
            $normalizedUniqueColumns = [];
            foreach (array_merge($uniqueColumns, [$keyName, $qualifiedKeyName]) as $col) {
                if (! is_string($col) || $col === '') {
                    continue;
                }

                $normalizedUniqueColumns[$col] = true;

                if (! str_contains($col, '.')) {
                    $normalizedUniqueColumns["{$table}.{$col}"] = true;
                } elseif (str_starts_with($col, "{$table}.")) {
                    $normalizedUniqueColumns[Str::after($col, "{$table}.")] = true;
                }
            }

            $hasUniqueOrder = false;
            foreach ($baseQuery->orders ?? [] as $order) {
                $orderCol = $order['column'] ?? null;
                if (is_string($orderCol) && isset($normalizedUniqueColumns[$orderCol])) {
                    $hasUniqueOrder = true;
                    break;
                }
            }

            if (! $hasUniqueOrder) {
                $eloquent->orderBy($qualifiedKeyName);
            }
        }

        $isSparse = ! $isDistinct
            && is_array($baseQuery->columns)
            && ! in_array('*', $baseQuery->columns, true)
            && ! in_array("{$table}.*", $baseQuery->columns, true);

        $autoSelectedColumns = [];

        if ($isSparse) {
            $internalColumns = [];

            foreach ($baseQuery->orders ?? [] as $order) {
                $orderCol = $order['column'] ?? null;
                if (! is_string($orderCol) || $orderCol === '') {
                    continue;
                }

                if (! str_contains($orderCol, '.')) {
                    $internalColumns[] = $orderCol;
                } elseif (str_starts_with($orderCol, "{$table}.")) {
                    $internalColumns[] = Str::after($orderCol, "{$table}.");
                }
            }

            foreach ($requiredForAppends as $appendCol) {
                if (is_string($appendCol) && $appendCol !== '') {
                    $internalColumns[] = str_starts_with($appendCol, "{$table}.")
                        ? Str::after($appendCol, "{$table}.")
                        : $appendCol;
                }
            }

            if ($model instanceof User) {
                array_push($internalColumns, 'is_member', 'start_date', 'end_date');
            } elseif ($model instanceof Achievement) {
                $internalColumns[] = 'id';
            }

            $selectedStrings = array_values(array_filter(
                $baseQuery->columns,
                static fn (mixed $col): bool => is_string($col),
            ));

            foreach (array_values(array_unique($internalColumns)) as $col) {
                if (
                    ! in_array($col, $selectedStrings, true)
                    && ! in_array("{$table}.{$col}", $selectedStrings, true)
                ) {
                    $eloquent->addSelect("{$table}.{$col}");
                    $autoSelectedColumns[] = $col;
                    $selectedStrings[] = "{$table}.{$col}";
                }
            }
        }

        $perPage = $this->resolvePerPage($request);
        $isBelongsToMany = $query instanceof BelongsToMany
            || ($query instanceof QueryBuilder && $query->getSubject() instanceof BelongsToMany);
        $columns = $isBelongsToMany && $isSparse ? [] : ['*'];

        $paginator = $query->cursorPaginate($perPage, $columns, 'cursor', $cursor);

        if ($autoSelectedColumns !== []) {
            $paginator->getCollection()->each(
                static fn (mixed $item) => $item instanceof Model
                    ? $item->makeHidden($autoSelectedColumns)
                    : null,
            );
        }

        return $paginator;
    }
}
