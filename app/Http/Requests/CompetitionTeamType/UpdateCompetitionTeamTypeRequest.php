<?php

namespace App\Http\Requests\CompetitionTeamType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetitionTeamTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'weight' => ['sometimes', 'integer'],
        ];
    }
}
