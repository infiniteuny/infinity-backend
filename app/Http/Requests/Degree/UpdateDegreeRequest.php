<?php

namespace App\Http\Requests\Degree;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDegreeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $degree = $this->route('degree');

        return [
            'code' => ['sometimes', 'string', Rule::unique('degrees', 'code')->ignore($degree)],
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
            'code' => [
                'description' => 'The unique code of the degree.',
            ],
            'name' => [
                'description' => 'The name of the degree.',
            ],
        ];
    }
}
