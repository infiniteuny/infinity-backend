<?php

namespace App\Http\Requests\CommunityGroupAdminMember;

use App\Models\CommunityGroupAdminMember;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCommunityGroupAdminMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userIdRules = ['sometimes', 'uuid', 'exists:users,id'];
        $communityGroupIdRules = ['sometimes', 'uuid', 'exists:community_groups,id'];

        if ($this->user()) {
            $routeMember = $this->route('community_group_admin_member');
            $member = $routeMember instanceof CommunityGroupAdminMember
                ? $routeMember
                : (is_string($routeMember) && Str::isUuid($routeMember)
                    ? CommunityGroupAdminMember::find($routeMember)
                    : null);
            $adminId = $member?->community_group_admin_id;
            $effectiveUserId = $this->has('user_id')
                ? $this->input('user_id')
                : $member?->user_id;
            $effectiveCommunityGroupId = $this->has('community_group_id')
                ? $this->input('community_group_id')
                : $member?->community_group_id;

            if (
                is_string($adminId) && Str::isUuid($adminId) &&
                is_string($effectiveUserId) && Str::isUuid($effectiveUserId) &&
                is_string($effectiveCommunityGroupId) && Str::isUuid($effectiveCommunityGroupId)
            ) {
                if ($this->has('user_id')) {
                    $userIdRules[] = Rule::unique('community_group_admin_members', 'user_id')
                        ->where('community_group_admin_id', $adminId)
                        ->where('community_group_id', $effectiveCommunityGroupId)
                        ->ignore($member);
                } elseif ($this->has('community_group_id')) {
                    $communityGroupIdRules[] = Rule::unique('community_group_admin_members', 'community_group_id')
                        ->where('community_group_admin_id', $adminId)
                        ->where('user_id', $effectiveUserId)
                        ->ignore($member);
                }
            }
        }

        return [
            'user_id' => $userIdRules,
            'community_group_id' => $communityGroupIdRules,
            'photo' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'animation' => ['sometimes', 'nullable', 'file', 'mimes:gif,apng,webp', 'max:5120'],
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
