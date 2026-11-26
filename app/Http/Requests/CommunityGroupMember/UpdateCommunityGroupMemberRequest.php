<?php

namespace App\Http\Requests\CommunityGroupMember;

use App\Models\User;
use App\Rules\OwnedEntity;
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

        if ($this->user()?->cannot('update-community-group-member')) {
            $userIdRules[] = new OwnedEntity(User::class, $this->user()->id);
        }

        return [
            'user_id' => $userIdRules,
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
            'user_id' => [
                'description' => 'The ID of the user belonging to the community group.',
            ],
        ];
    }
}
