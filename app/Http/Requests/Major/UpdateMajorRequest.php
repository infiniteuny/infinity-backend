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
        $majorId = $this->route('major');

        return [
            'degree_id' => ['sometimes', 'uuid', 'exists:degrees,id'],
            'faculty_id' => ['sometimes', 'uuid', 'exists:faculties,id'],
            'code' => ['sometimes', 'string', 'unique:majors,code,'.$majorId],
            'name' => ['sometimes', 'string'],
        ];
    }
}
