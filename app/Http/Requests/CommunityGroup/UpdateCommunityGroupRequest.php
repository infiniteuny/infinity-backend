<?php

namespace App\Http\Requests\CommunityGroup;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommunityGroupRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
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
        $communityGroup = $this->route('community_group');

        return [
            'name' => ['sometimes', 'string', Rule::unique('community_groups', 'name')->ignore($communityGroup)],
            'priority' => ['sometimes', 'integer', 'between:0,127'],
            'description' => ['sometimes', 'string'],
            'logo' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
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
