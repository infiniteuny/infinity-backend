<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Associative implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If keys are a consecutive list of numbers, it’s not an associative array.
        if (! is_array($value) || array_keys($value) === range(0, count($value) - 1)) {
            $fail("The {$attribute} field must be an associative array.");
        }
    }
}
