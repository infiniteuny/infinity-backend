<?php

namespace App\Http\Requests\CompetitionInstance;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetitionInstanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'competition_id' => ['sometimes', 'uuid', 'exists:competitions,id'],
            'name' => ['sometimes', 'string'],
            'description' => ['sometimes', 'string'],
            'url' => ['sometimes', 'nullable', 'string', 'url:http,https'],
            'organizer' => ['sometimes', 'string'],
            'organizer_type_id' => ['sometimes', 'uuid', 'exists:competition_organizer_types,id'],
            'logo' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'start_date' => ['sometimes', 'date', 'before_or_equal:end_date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'location' => ['sometimes', 'string'],
        ];
    }
}
