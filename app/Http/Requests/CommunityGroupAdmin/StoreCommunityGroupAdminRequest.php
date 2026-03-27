<?php

namespace App\Http\Requests\CommunityGroupAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityGroupAdminRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'unique:community_group_admins,year'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
