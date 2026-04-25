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

        return [
            'year' => ['sometimes', 'integer', Rule::unique('community_group_admins', 'year')->ignore($communityGroupAdmin->id)],
            'is_active' => [
                'sometimes',
                'accepted',
                new CannotDeactivateActive($communityGroupAdmin->is_active ?? false),
            ],
        ];
    }
}
