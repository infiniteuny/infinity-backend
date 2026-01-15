<?php

namespace App\Http\Requests\CoreTeam;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCoreTeamRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $coreTeamId = $this->route('core_team');

        return [
            'year' => ['sometimes', 'integer', 'unique:core_teams,year,'.$coreTeamId],
            'is_active' => ['sometimes', 'accepted'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'is_active.accepted' => 'The is active field must be true.',
        ];
    }
}
