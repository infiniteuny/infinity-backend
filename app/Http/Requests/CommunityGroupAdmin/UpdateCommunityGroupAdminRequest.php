<?php

namespace App\Http\Requests\CommunityGroupAdmin;

use App\Models\CommunityGroupAdmin;
use App\Rules\NoDeactivateActive;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCommunityGroupAdminRequest extends FormRequest
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
        $communityGroupAdmin = null;

        if ($this->user()) {
            $routeCommunityGroupAdmin = $this->route('community_group_admin');
            $communityGroupAdmin = $routeCommunityGroupAdmin instanceof CommunityGroupAdmin
                ? $routeCommunityGroupAdmin
                : (is_string($routeCommunityGroupAdmin) && Str::isUuid($routeCommunityGroupAdmin)
                    ? CommunityGroupAdmin::find($routeCommunityGroupAdmin)
                    : null);
        }

        $yearUniqueRule = Rule::unique('community_group_admins', 'year');

        if ($communityGroupAdmin?->id) {
            $yearUniqueRule->ignore($communityGroupAdmin->id);
        }

        return [
            'year' => ['sometimes', 'integer', $yearUniqueRule],
            'is_active' => [
                'sometimes',
                'boolean',
                new NoDeactivateActive($communityGroupAdmin->is_active ?? false),
            ],
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
            'year' => [
                'description' => 'The service period year of the community group administration.',
            ],
            'is_active' => [
                'description' => 'Whether this community group administration period is currently active.',
            ],
        ];
    }
}
