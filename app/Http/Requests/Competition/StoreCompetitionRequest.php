<?php

namespace App\Http\Requests\Competition;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompetitionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'url' => ['nullable', 'string', 'url:http,https'],
            'organizer' => ['required', 'string'],
            'organizer_type_id' => ['required', 'uuid', 'exists:competition_organizer_types,id'],
            'logo' => ['required', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
