<?php

namespace App\Http\Requests\CommunityGroupAdmin;

use App\Models\Group;
use App\Traits\HasBooleanInput;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityGroupAdminRequest extends FormRequest
{
    use HasBooleanInput;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->prepareBooleanInput(['is_active']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => [
                'bail',
                'required',
                'integer',
                'between:1901,2155',
                'unique:community_group_admins,year',
                static function (string $attribute, mixed $value, Closure $fail): void {
                    if ((! is_int($value) && ! is_string($value)) || filter_var($value, FILTER_VALIDATE_INT) === false) {
                        return;
                    }

                    $year = (int) $value;
                    if ($year < 1901 || $year > 2155) {
                        return;
                    }

                    try {
                        $exists = Group::query()
                            ->where('name', 'CG Admin '.$year)
                            ->where('guard_name', 'api')
                            ->exists();
                    } catch (QueryException) {
                        return;
                    }

                    if ($exists) {
                        $fail('The group name for this year is already taken.');
                    }
                },
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Get the body parameters for API documentation.
     *
     * @return array<string, array{description: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'year' => [
                'description' => 'The service period year of the community group administration.',
            ],
            'is_active' => [
                'description' => 'Whether this community group administration period is currently active.',
            ],
        ];
    }
}
