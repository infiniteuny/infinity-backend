<?php

namespace App\Http\Requests\CoreTeam;

use App\Traits\HasBooleanInput;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCoreTeamRequest extends FormRequest
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
            'year' => ['bail', 'required', 'integer', 'between:1901,2155', 'unique:core_teams,year'],
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
                'description' => 'The service period year of the core team.',
            ],
            'is_active' => [
                'description' => 'Whether this core team period is currently active.',
            ],
        ];
    }
}
