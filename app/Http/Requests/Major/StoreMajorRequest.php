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
            'code' => ['required', 'string', 'unique:majors'],
            'name' => ['required', 'string'],
        ];
    }
}
