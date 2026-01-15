<?php

namespace App\Http\Requests\CoreTeam;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCoreTeamRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $coreTeam = $this->route('core_team');

        return [
            'year' => ['sometimes', 'integer', Rule::unique('core_teams', 'year')->ignore($coreTeam)],
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
