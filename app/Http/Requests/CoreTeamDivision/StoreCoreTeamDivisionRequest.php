<?php

namespace App\Http\Requests\CoreTeamDivision;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCoreTeamDivisionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:core_team_divisions,name'],
            'priority' => ['required', 'integer', 'between:0,127'],
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
            'name' => [
                'description' => 'The name of the core team division.',
            ],
            'priority' => [
                'description' => 'The display priority order of the core team division.',
            ],
        ];
    }
}
