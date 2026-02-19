<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'leader_id' => ['required', 'uuid', 'exists:users,id'],
            'team_type_id' => ['required', 'uuid', 'exists:competition_team_types,id'],
            'name' => ['required', 'string'],
            'is_personal' => ['required', 'boolean'],
        ];
    }
}
