<?php

namespace App\Http\Requests\CoreTeamMember;

use App\Models\CoreTeamMember;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCoreTeamMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userIdRules = ['sometimes', 'uuid', 'exists:users,id'];

        if ($this->user()) {
            $routeMember = $this->route('core_team_member');
            $member = $routeMember instanceof CoreTeamMember
                ? $routeMember
                : (is_string($routeMember) && Str::isUuid($routeMember)
                    ? CoreTeamMember::find($routeMember)
                    : null);
            $coreTeamId = $member?->core_team_id;
            $userId = $this->input('user_id');

            if (
                is_string($coreTeamId) && Str::isUuid($coreTeamId) &&
                is_string($userId) && Str::isUuid($userId)
            ) {
                $userIdRules[] = Rule::unique('core_team_members', 'user_id')
                    ->where('core_team_id', $coreTeamId)
                    ->ignore($member);
            }
        }

        return [
            'user_id' => $userIdRules,
            'core_team_division_id' => ['sometimes', 'uuid', 'exists:core_team_divisions,id'],
            'photo' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'animation' => ['sometimes', 'nullable', 'file', 'mimes:gif,apng,webp', 'max:5120'],
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
