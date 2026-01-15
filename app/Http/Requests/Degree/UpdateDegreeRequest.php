<?php

namespace App\Http\Requests\Degree;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDegreeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $degree = $this->route('degree');

        return [
            'code' => ['sometimes', 'string', Rule::unique('degrees', 'code')->ignore($degree)],
            'name' => ['sometimes', 'string'],
        ];
    }
}
