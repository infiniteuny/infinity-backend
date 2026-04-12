<?php

namespace App\Http\Requests\CommunityGroupMember;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunityGroupMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userIdRules = ['sometimes', 'uuid', 'exists:users,id'];

        if ($this->user()?->cannot('create-community-group-member')) {
            $userIdRules[] = 'in:'.$this->user()->id;
        }

        return [
            'user_id' => $userIdRules,
        ];
    }
}
