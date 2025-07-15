<?php

namespace App\Http\Requests\CommunityGroupAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunityGroupAdminRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => ['sometimes', 'integer', 'unique:community_group_admins,year'],
            'is_active' => ['sometimes', 'accepted'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'is_active.accepted' => 'The is active field must be true.',
        ];
    }
}
