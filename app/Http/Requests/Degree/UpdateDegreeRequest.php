<?php

namespace App\Http\Requests\Degree;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDegreeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $degreeId = $this->route('degree');

        return [
            'code' => ['sometimes', 'string', 'unique:degrees,code,'.$degreeId],
            'name' => ['sometimes', 'string'],
        ];
    }
}
