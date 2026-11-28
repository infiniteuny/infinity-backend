<?php

namespace App\Http\Requests\Competition;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetitionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'shortname' => ['sometimes', 'nullable', 'string', 'max:255'],
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
                'description' => 'The name of the competition series.',
            ],
            'shortname' => [
                'description' => 'The short name or acronym of the competition series.',
            ],
        ];
    }
}
