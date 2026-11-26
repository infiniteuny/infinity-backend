<?php

namespace App\Http\Requests\UserGroup;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreUserGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $groupIdRules = ['required', 'uuid', 'exists:groups,id'];

        if ($this->user()) {
            $routeUser = $this->route('user');
            $userId = $routeUser instanceof User
                ? $routeUser->id
                : $routeUser;
            $groupId = $this->input('group_id');

            if (
                is_string($userId) && Str::isUuid($userId) &&
                is_string($groupId) && Str::isUuid($groupId)
            ) {
                $groupIdRules[] = Rule::unique('user_groups', 'group_id')
                    ->where('user_id', $userId);
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
