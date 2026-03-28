<?php

namespace App\Http\Requests\CoreTeam;

use App\Models\CoreTeam;
use App\Rules\CannotDeactivateActive;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCoreTeamRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $coreTeamId = $this->route('core_team');
        $coreTeam = CoreTeam::find($coreTeamId);

        return [
            'year' => ['sometimes', 'integer', Rule::unique('core_teams', 'year')->ignore($coreTeamId)],
            'is_active' => [
                'sometimes',
                'boolean',
                new CannotDeactivateActive($coreTeam->is_active ?? false),
            ],
        ];
    }
}
