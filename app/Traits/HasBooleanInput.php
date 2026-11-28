<?php

namespace App\Traits;

trait HasBooleanInput
{
    /**
     * Coerce valid boolean string and 0/1 integer inputs into native booleans
     * without turning null, empty strings, or invalid values into false.
     *
     * @param  array<int, string>  $fields
     */
    protected function prepareBooleanInput(array $fields): void
    {
        $coerced = [];

        foreach ($fields as $field) {
            if (! $this->has($field)) {
                continue;
            }

            $value = $this->input($field);

            if (is_bool($value)) {
                continue;
            }

            if (is_int($value)) {
                if ($value === 0 || $value === 1) {
                    $coerced[$field] = (bool) $value;
                }

                continue;
            }

            if (is_string($value)) {
                if (trim($value) === '') {
                    $coerced[$field] = null;

                    continue;
                }

                $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                if ($parsed !== null) {
                    $coerced[$field] = $parsed;
                }
            }
        }

        if ($coerced !== []) {
            $this->merge($coerced);
        }
    }
}
