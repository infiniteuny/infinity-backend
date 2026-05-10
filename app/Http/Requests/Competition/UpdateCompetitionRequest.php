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
            'name' => ['sometimes', 'string'],
            'shortname' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'string'],
        ];
    }
}
