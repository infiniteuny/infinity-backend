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
        $yearUniqueRule = Rule::unique('core_teams', 'year');

        if ($coreTeam?->id) {
            $yearUniqueRule->ignore($coreTeam->id);
        }

        return [
            'year' => ['sometimes', 'integer', $yearUniqueRule],
            'is_active' => [
                'sometimes',
                'boolean',
                new CannotDeactivateActive($coreTeam->is_active ?? false),
            ],
        ];
    }
}
