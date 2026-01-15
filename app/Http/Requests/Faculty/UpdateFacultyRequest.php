<?php

namespace App\Http\Requests\Faculty;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFacultyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $facultyId = $this->route('faculty')->id;

        return [
            'code' => ['sometimes', 'string', 'unique:faculties,code,'.$facultyId],
            'name' => ['sometimes', 'string'],
        ];
    }
}
