<?php

namespace App\Http\Requests\CommunityGroupMember;

use App\Models\CommunityGroup;
use App\Models\User;
use App\Rules\OwnedEntity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCommunityGroupMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userIdRules = ['required', 'uuid', 'exists:users,id'];

        if ($this->user()?->cannot('create-community-group-member')) {
            $userIdRules[] = new OwnedEntity(User::class, $this->user()->id);
        }

        if ($this->user()) {
            $routeCommunityGroup = $this->route('community_group');
            $communityGroupId = $routeCommunityGroup instanceof CommunityGroup
                ? $routeCommunityGroup->id
                : $routeCommunityGroup;
            $userId = $this->input('user_id');

            if (
                is_string($communityGroupId) && Str::isUuid($communityGroupId) &&
                is_string($userId) && Str::isUuid($userId)
            ) {
                $userIdRules[] = Rule::unique('community_group_members', 'user_id')
                    ->where('community_group_id', $communityGroupId);
            }
        }

        return [
            'user_id' => $userIdRules,
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
                'description' => 'The ID of the user belonging to the community group.',
            ],
        ];
    }
}
