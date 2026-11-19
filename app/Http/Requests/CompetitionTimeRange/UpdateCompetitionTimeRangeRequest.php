<?php

namespace App\Http\Requests\CompetitionTimeRange;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetitionTimeRangeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'weight' => ['sometimes', 'integer', 'between:0,2147483647'],
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
                'description' => 'The name of the competition time range.',
            ],
            'weight' => [
                'description' => 'The scoring weight of the competition time range.',
            ],
        ];
    }
}
