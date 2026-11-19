<?php

namespace App\Http\Requests\Persona;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePersonaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'priority' => ['required', 'integer', 'between:0,127'],
            'description' => ['required', 'string'],
            'logo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
                'description' => 'The name of the persona.',
            ],
            'priority' => [
                'description' => 'The display priority order of the persona.',
            ],
            'description' => [
                'description' => 'The description of the persona.',
            ],
            'logo' => [
                'description' => 'The logo image of the persona.',
            ],
        ];
    }
}
