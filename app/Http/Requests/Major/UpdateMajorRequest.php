<?php

namespace App\Http\Requests\Major;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMajorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $major = $this->route('major');

        return [
            'degree_id' => ['sometimes', 'uuid', 'exists:degrees,id'],
            'faculty_id' => ['sometimes', 'uuid', 'exists:faculties,id'],
            'code' => ['sometimes', 'string', Rule::unique('majors', 'code')->ignore($major)],
            'name' => ['sometimes', 'string'],
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
