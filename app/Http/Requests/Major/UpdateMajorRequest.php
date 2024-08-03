<?php

namespace App\Http\Requests\Major;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMajorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'degree_id' => ['sometimes', 'exists:degrees,id'],
            'faculty_id' => ['sometimes', 'exists:faculties,id'],
            'code' => ['sometimes', 'string', 'unique:majors'],
            'name' => ['sometimes', 'string'],
        ];
    }
}
