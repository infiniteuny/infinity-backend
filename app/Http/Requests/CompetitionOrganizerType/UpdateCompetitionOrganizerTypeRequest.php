<?php

namespace App\Http\Requests\CompetitionOrganizerType;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetitionOrganizerTypeRequest extends FormRequest
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
}
