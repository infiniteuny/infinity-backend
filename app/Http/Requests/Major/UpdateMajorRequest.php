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
            'code' => 'required|integer|unique:majors',
            'name' => 'required|string',
            'degree_id' => 'required|exists:degrees,id',
            'faculty_id' => 'required|exists:faculties,id',
        ];
    }
}
