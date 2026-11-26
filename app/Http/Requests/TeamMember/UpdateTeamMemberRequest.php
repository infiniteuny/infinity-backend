<?php

namespace App\Http\Requests\TeamMember;

use App\Models\TeamMember;
use App\Models\User;
use App\Rules\OwnedEntity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateTeamMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $userIdRules = ['sometimes', 'uuid', 'exists:users,id'];

        if ($user?->cannot('update-team-member')) {
            $routeTeamMember = $this->route('team_member');
            $teamMember = $routeTeamMember instanceof TeamMember
                ? $routeTeamMember
                : (is_string($routeTeamMember) && Str::isUuid($routeTeamMember)
                    ? TeamMember::find($routeTeamMember)
                    : null);

            if ($teamMember?->team?->leader_id !== $user->id) {
                $userIdRules[] = new OwnedEntity(User::class, $user->id);
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
