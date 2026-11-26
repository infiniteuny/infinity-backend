<?php

namespace App\Http\Requests\Permission;

use App\Models\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $nameRules = ['sometimes', 'string', 'max:255'];
        $guardNameRules = ['sometimes', 'string', 'in:api'];

        if ($this->user()) {
            $routePermission = $this->route('permission');
            $permission = $routePermission instanceof Permission
                ? $routePermission
                : (is_string($routePermission) && Str::isUuid($routePermission)
                    ? Permission::find($routePermission)
                    : null);
            $ignorePermission = $permission ?? $routePermission;

            $effectiveGuardName = $this->has('guard_name')
                ? $this->input('guard_name')
                : ($permission?->guard_name ?? 'api');
            $effectiveName = $this->has('name')
                ? $this->input('name')
                : $permission?->name;

            if ($this->has('name') && is_string($effectiveGuardName)) {
                $nameRules[] = Rule::unique('permissions', 'name')
                    ->where('guard_name', $effectiveGuardName)
                    ->ignore($ignorePermission);
            } elseif ($this->has('guard_name') && is_string($effectiveName)) {
                $guardNameRules[] = Rule::unique('permissions', 'guard_name')
                    ->where('name', $effectiveName)
                    ->ignore($ignorePermission);
            }
        }

        return [
            'name' => $nameRules,
            'guard_name' => $guardNameRules,
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
            'name' => [
                'description' => 'The name of the permission.',
            ],
            'guard_name' => [
                'description' => 'The authentication guard name for the permission.',
            ],
        ];
    }
}
