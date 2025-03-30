<?php

namespace App\Http\Requests\GroupPermission;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupPermissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'permission_id' => ['sometimes', 'uuid', 'exists:permissions,id'],
        ];
    }
}
