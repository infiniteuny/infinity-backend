<?php

namespace App\Http\Requests\Team;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $individualTeamTypeId = config('app.individual_team_type_id');
        $teamTypeExistsRule = Rule::exists('competition_team_types', 'id');

        if ($individualTeamTypeId) {
            $teamTypeExistsRule->whereNot('id', $individualTeamTypeId);
        }

        return [
            'leader_id' => ['sometimes', 'uuid', 'exists:users,id'],
            'team_type_id' => ['sometimes', 'uuid', $teamTypeExistsRule],
            'name' => ['sometimes', 'string', 'max:255'],
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
            'leader_id' => [
                'description' => 'The ID of the user who leads the team.',
            ],
            'team_type_id' => [
                'description' => 'The ID of the team type.',
            ],
            'name' => [
                'description' => 'The name of the team.',
            ],
        ];
    }
}
