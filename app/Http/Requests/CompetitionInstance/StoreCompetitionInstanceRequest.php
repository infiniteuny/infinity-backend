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
            'name' => ['required', 'string', 'max:255'],
            'shortname' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'url' => ['nullable', 'string', 'url:http,https'],
            'organizer' => ['required', 'string', 'max:255'],
            'organizer_type_id' => ['required', 'uuid', 'exists:competition_organizer_types,id'],
            'logo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'location' => ['required', 'string', 'max:255'],
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
            'competition_id' => [
                'description' => 'The ID of the parent competition series.',
            ],
            'name' => [
                'description' => 'The name of the competition instance.',
            ],
            'shortname' => [
                'description' => 'The short name or acronym of the competition instance.',
            ],
            'description' => [
                'description' => 'The description of the competition instance.',
            ],
            'url' => [
                'description' => 'The official website URL of the competition instance.',
            ],
            'organizer' => [
                'description' => 'The name of the competition organizer.',
            ],
            'organizer_type_id' => [
                'description' => 'The ID of the competition organizer type.',
            ],
            'logo' => [
                'description' => 'The logo image of the competition instance.',
            ],
            'start_date' => [
                'description' => 'The start date of the competition instance.',
            ],
            'end_date' => [
                'description' => 'The end date of the competition instance.',
            ],
            'location' => [
                'description' => 'The location or venue of the competition instance.',
            ],
        ];
    }
}
