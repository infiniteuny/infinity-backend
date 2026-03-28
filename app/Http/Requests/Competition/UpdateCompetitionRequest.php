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
            'description' => ['sometimes', 'string'],
            'url' => ['sometimes', 'nullable', 'string', 'url:http,https'],
            'organizer' => ['sometimes', 'string'],
            'organizer_type_id' => ['sometimes', 'uuid', 'exists:competition_organizer_types,id'],
            'logo' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
