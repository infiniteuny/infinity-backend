<?php

namespace App\Http\Requests\CommunityGroupAdminMember;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityGroupAdminMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'community_group_id' => ['required', 'uuid', 'exists:community_groups,id'],
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'animation' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
