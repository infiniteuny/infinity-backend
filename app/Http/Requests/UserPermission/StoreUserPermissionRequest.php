<?php

namespace App\Http\Requests\UserPermission;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreUserPermissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $permissionIdRules = ['required', 'uuid', 'exists:permissions,id'];

        if ($this->user()) {
            $routeUser = $this->route('user');
            $userId = $routeUser instanceof User
                ? $routeUser->id
                : $routeUser;
            $permissionId = $this->input('permission_id');

            if (
                is_string($userId) && Str::isUuid($userId) &&
                is_string($permissionId) && Str::isUuid($permissionId)
            ) {
                $permissionIdRules[] = Rule::unique('user_permissions', 'permission_id')
                    ->where('user_id', $userId);
            }
        }

        return [
            'permission_id' => $permissionIdRules,
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
            'permission_id' => [
                'description' => 'The ID of the permission to assign to the user.',
            ],
        ];
    }
}
