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
        $routeCoreTeam = $this->route('core_team');
        $coreTeam = $routeCoreTeam instanceof CoreTeam
            ? $routeCoreTeam
            : CoreTeam::find($routeCoreTeam);

        return [
            'year' => ['sometimes', 'integer', Rule::unique('core_teams', 'year')->ignore($coreTeam->id)],
            'is_active' => [
                'sometimes',
                'boolean',
                new CannotDeactivateActive($coreTeam->is_active ?? false),
            ],
        ];
    }
}
