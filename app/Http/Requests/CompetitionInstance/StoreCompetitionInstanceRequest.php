<?php

namespace App\Http\Requests\CompetitionInstance;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompetitionInstanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'competition_id' => ['required', 'uuid', 'exists:competitions,id'],
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'url' => ['nullable', 'string', 'url:http,https'],
            'organizer' => ['required', 'string'],
            'organizer_type_id' => ['required', 'uuid', 'exists:competition_organizer_types,id'],
            'logo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'location' => ['required', 'string'],
        ];
    }
}
