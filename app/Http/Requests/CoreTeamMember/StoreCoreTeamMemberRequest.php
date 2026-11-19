<?php

namespace App\Http\Requests\CoreTeamMember;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCoreTeamMemberRequest extends FormRequest
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
            'core_team_division_id' => ['required', 'uuid', 'exists:core_team_divisions,id'],
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'animation' => ['nullable', 'file', 'mimes:gif,apng,webp', 'max:5120'],
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
                'description' => 'The ID of the user serving as a core team member.',
            ],
            'core_team_division_id' => [
                'description' => 'The ID of the core team division the member belongs to.',
            ],
            'photo' => [
                'description' => 'The profile photo of the core team member.',
            ],
            'animation' => [
                'description' => 'The animated profile media of the core team member.',
            ],
        ];
    }
}
