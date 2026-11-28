<?php

namespace App\Http\Requests\Config;

use App\Traits\HasBooleanInput;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfigRequest extends FormRequest
{
    use HasBooleanInput;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->prepareBooleanInput(['is_private']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $config = $this->route('config');

        return [
            'key' => ['sometimes', 'string', 'max:255', Rule::unique('configs', 'key')->ignore($config)],
            'value' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'in:STRING,INTEGER,BOOLEAN'],
            'is_private' => ['sometimes', 'boolean'],
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
