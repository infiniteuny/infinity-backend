<?php

namespace App\Http\Requests\UserPermission;

use App\Models\UserPermission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateUserPermissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $permissionIdRules = ['sometimes', 'uuid', 'exists:permissions,id'];

        if ($this->user()) {
            $routeUserPermission = $this->route('user_permission');
            $userPermission = $routeUserPermission instanceof UserPermission
                ? $routeUserPermission
                : (is_string($routeUserPermission) && Str::isUuid($routeUserPermission)
                    ? UserPermission::find($routeUserPermission)
                    : null);
            $userId = $userPermission?->user_id;
            $permissionId = $this->input('permission_id');

            if (
                is_string($userId) && Str::isUuid($userId) &&
                is_string($permissionId) && Str::isUuid($permissionId)
            ) {
                $permissionIdRules[] = Rule::unique('user_permissions', 'permission_id')
                    ->where('user_id', $userId)
                    ->ignore($userPermission);
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
