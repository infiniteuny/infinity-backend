<?php

namespace App\Http\Requests\UserGroup;

use App\Models\UserGroup;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateUserGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $groupIdRules = ['sometimes', 'uuid', 'exists:groups,id'];

        if ($this->user()) {
            $routeUserGroup = $this->route('user_group');
            $userGroup = $routeUserGroup instanceof UserGroup
                ? $routeUserGroup
                : (is_string($routeUserGroup) && Str::isUuid($routeUserGroup)
                    ? UserGroup::find($routeUserGroup)
                    : null);
            $userId = $userGroup?->user_id;
            $groupId = $this->input('group_id');

            if (
                is_string($userId) && Str::isUuid($userId) &&
                is_string($groupId) && Str::isUuid($groupId)
            ) {
                $groupIdRules[] = Rule::unique('user_groups', 'group_id')
                    ->where('user_id', $userId)
                    ->ignore($userGroup);
            }
        }

        return [
            'group_id' => $groupIdRules,
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
            'group_id' => [
                'description' => 'The ID of the group to assign to the user.',
            ],
        ];
    }
}
