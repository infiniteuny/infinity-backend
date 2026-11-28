<?php

namespace App\Http\Requests\CommunityGroup;

use App\Traits\HasBooleanInput;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityGroupRequest extends FormRequest
{
    use HasBooleanInput;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->prepareBooleanInput(['is_active']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:community_groups,name'],
            'priority' => ['required', 'integer', 'between:0,127'],
            'description' => ['required', 'string'],
            'logo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['required', 'boolean'],
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
            'name' => [
                'description' => 'The name of the community group.',
            ],
            'priority' => [
                'description' => 'The display priority order of the community group.',
            ],
            'description' => [
                'description' => 'The description of the community group.',
            ],
            'logo' => [
                'description' => 'The logo image of the community group.',
            ],
            'is_active' => [
                'description' => 'Whether the community group is currently active.',
            ],
        ];
    }
}
