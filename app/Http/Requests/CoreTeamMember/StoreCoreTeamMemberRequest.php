<?php

namespace App\Http\Requests\CoreTeamMember;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoreTeamMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'core_team_id' => ['required', 'uuid', 'exists:core_teams,id'],
            'core_team_division_id' => ['required', 'uuid', 'exists:core_team_divisions,id'],
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'animation' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
