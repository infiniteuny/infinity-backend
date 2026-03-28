<?php

namespace App\Http\Requests\Faculty;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFacultyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $faculty = $this->route('faculty');

        return [
            'code' => ['sometimes', 'string', Rule::unique('faculties', 'code')->ignore($faculty)],
            'name' => ['sometimes', 'string'],
        ];
    }
}
