<?php

namespace App\Http\Requests\GroupPermission;

use App\Models\GroupPermission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateGroupPermissionRequest extends FormRequest
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
            $routeGroupPermission = $this->route('group_permission');
            $groupPermission = $routeGroupPermission instanceof GroupPermission
                ? $routeGroupPermission
                : (is_string($routeGroupPermission) && Str::isUuid($routeGroupPermission)
                    ? GroupPermission::find($routeGroupPermission)
                    : null);
            $groupId = $groupPermission?->group_id;
            $permissionId = $this->input('permission_id');

            if (
                is_string($groupId) && Str::isUuid($groupId) &&
                is_string($permissionId) && Str::isUuid($permissionId)
            ) {
                $permissionIdRules[] = Rule::unique('group_permissions', 'permission_id')
                    ->where('group_id', $groupId)
                    ->ignore($groupPermission);
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
                'description' => 'The ID of the permission to assign to the group.',
            ],
        ];
    }
}
