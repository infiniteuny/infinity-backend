<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'leader_id' => ['sometimes', 'uuid', 'exists:users,id'],
            'team_type_id' => ['sometimes', 'uuid', 'exists:competition_team_types,id'],
            'name' => ['sometimes', 'string'],
            'is_personal' => ['sometimes', 'boolean'],
        ];
    }
}
