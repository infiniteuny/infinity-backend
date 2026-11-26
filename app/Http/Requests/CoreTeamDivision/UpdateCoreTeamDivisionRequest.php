<?php

namespace App\Http\Requests\CoreTeamDivision;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCoreTeamDivisionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $coreTeamDivision = $this->route('core_team_division');

        return [
            'name' => ['sometimes', 'string', Rule::unique('core_team_divisions', 'name')->ignore($coreTeamDivision)],
            'priority' => ['sometimes', 'integer', 'between:0,127'],
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
