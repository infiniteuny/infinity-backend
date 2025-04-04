<?php

namespace App\Http\Requests\CompetitionScale;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetitionScaleRequest extends FormRequest
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
            'weight' => ['sometimes', 'integer', 'between:0,2147483647'],
        ];
    }
}
