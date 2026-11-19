<?php

namespace App\Http\Requests\Team;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_personal')) {
            $this->merge([
                'is_personal' => filter_var($this->input('is_personal'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }

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
            'leader_id' => ['required', 'uuid', 'exists:users,id'],
            'team_type_id' => ['required', 'uuid', $teamTypeExistsRule],
            'name' => ['required', 'string'],
            'is_personal' => ['required', 'boolean'],
        ];
    }
}
