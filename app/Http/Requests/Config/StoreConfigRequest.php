<?php

namespace App\Http\Requests\Config;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreConfigRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_private')) {
            $this->merge([
                'is_private' => filter_var($this->input('is_private'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'unique:configs,key'],
            'value' => ['required', 'string'],
            'type' => ['required', 'string', 'in:STRING,INTEGER,BOOLEAN'],
            'is_private' => ['required', 'boolean'],
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
            'key' => [
                'description' => 'The unique key identifying the configuration entry.',
            ],
            'value' => [
                'description' => 'The value of the configuration entry.',
            ],
            'type' => [
                'description' => 'The data type of the configuration value.',
            ],
            'is_private' => [
                'description' => 'Whether the configuration entry is restricted from public access.',
            ],
        ];
    }
}
