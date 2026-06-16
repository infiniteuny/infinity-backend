<?php

namespace App\Http\Requests\CommunityGroupAdmin;

use App\Models\CommunityGroupAdmin;
use App\Rules\CannotDeactivateActive;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
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
        $routeCommunityGroupAdmin = $this->route('community_group_admin');
        $communityGroupAdmin = $routeCommunityGroupAdmin instanceof CommunityGroupAdmin
            ? $routeCommunityGroupAdmin
            : CommunityGroupAdmin::find($routeCommunityGroupAdmin);
        $yearUniqueRule = Rule::unique('community_group_admins', 'year');

        if ($communityGroupAdmin?->id) {
            $yearUniqueRule->ignore($communityGroupAdmin->id);
        }

        return [
            'year' => ['sometimes', 'integer', $yearUniqueRule],
            'is_active' => [
                'sometimes',
                'boolean',
                new CannotDeactivateActive($communityGroupAdmin->is_active ?? false),
            ],
        ];
    }
}
