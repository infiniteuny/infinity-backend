<?php

namespace App\Http\Requests\TeamMember;

use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreTeamMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userIdRules = ['required', 'uuid', 'exists:users,id'];

        if ($this->user()) {
            $routeTeam = $this->route('team');
            $teamId = $routeTeam instanceof Team
                ? $routeTeam->id
                : $routeTeam;
            $userId = $this->input('user_id');

            if (
                is_string($teamId) && Str::isUuid($teamId) &&
                is_string($userId) && Str::isUuid($userId)
            ) {
                $userIdRules[] = Rule::unique('team_members', 'user_id')
                    ->where('team_id', $teamId);
            }
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
                'description' => 'The ID of the user belonging to the team.',
            ],
        ];
    }
}
