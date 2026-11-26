<?php

namespace App\Http\Requests\CommunityGroupAdminMember;

use App\Models\CommunityGroupAdmin;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCommunityGroupAdminMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userIdRules = ['required', 'uuid', 'exists:users,id'];

        if ($this->user()) {
            $routeAdmin = $this->route('community_group_admin');
            $communityGroupAdminId = $routeAdmin instanceof CommunityGroupAdmin
                ? $routeAdmin->id
                : $routeAdmin;
            $userId = $this->input('user_id');
            $communityGroupId = $this->input('community_group_id');

            if (
                is_string($communityGroupAdminId) && Str::isUuid($communityGroupAdminId) &&
                is_string($userId) && Str::isUuid($userId) &&
                is_string($communityGroupId) && Str::isUuid($communityGroupId)
            ) {
                $userIdRules[] = Rule::unique('community_group_admin_members', 'user_id')
                    ->where('community_group_admin_id', $communityGroupAdminId)
                    ->where('community_group_id', $communityGroupId);
            }
        }

        return [
            'user_id' => $userIdRules,
            'community_group_id' => ['required', 'uuid', 'exists:community_groups,id'],
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'animation' => ['nullable', 'file', 'mimes:gif,apng,webp', 'max:5120'],
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
            'user_id' => [
                'description' => 'The ID of the user serving as a community group admin member.',
            ],
            'community_group_id' => [
                'description' => 'The ID of the community group administered by the member.',
            ],
            'photo' => [
                'description' => 'The profile photo of the community group admin member.',
            ],
            'animation' => [
                'description' => 'The animated profile media of the community group admin member.',
            ],
        ];
    }
}
