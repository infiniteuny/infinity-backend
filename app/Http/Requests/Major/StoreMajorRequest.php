<?php

namespace App\Http\Requests\Major;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMajorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'degree_id' => ['required', 'uuid', 'exists:degrees,id'],
            'faculty_id' => ['required', 'uuid', 'exists:faculties,id'],
            'code' => ['required', 'string', 'max:255', 'unique:majors'],
            'name' => ['required', 'string', 'max:255'],
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
            'degree_id' => [
                'description' => 'The ID of the degree associated with the major.',
            ],
            'faculty_id' => [
                'description' => 'The ID of the faculty the major belongs to.',
            ],
            'code' => [
                'description' => 'The unique code of the major.',
            ],
            'name' => [
                'description' => 'The name of the major.',
            ],
        ];
    }
}
