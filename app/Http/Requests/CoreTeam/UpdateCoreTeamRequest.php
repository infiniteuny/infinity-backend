<?php

namespace App\Http\Requests\CoreTeam;

use App\Models\CoreTeam;
use App\Rules\NoDeactivateActive;
use App\Traits\HasBooleanInput;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCoreTeamRequest extends FormRequest
{
    use HasBooleanInput;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->prepareBooleanInput(['is_active']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $coreTeam = null;

        if ($this->user()) {
            $routeCoreTeam = $this->route('core_team');
            $coreTeam = $routeCoreTeam instanceof CoreTeam
                ? $routeCoreTeam
                : (is_string($routeCoreTeam) && Str::isUuid($routeCoreTeam)
                    ? CoreTeam::find($routeCoreTeam)
                    : null);
        }

        $yearUniqueRule = Rule::unique('core_teams', 'year');

        if ($coreTeam?->id) {
            $yearUniqueRule->ignore($coreTeam->id);
        }

        return [
            'year' => ['bail', 'sometimes', 'integer', 'between:1901,2155', $yearUniqueRule],
            'is_active' => [
                'sometimes',
                'boolean',
                new NoDeactivateActive($coreTeam->is_active ?? false),
            ],
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
            'year' => [
                'description' => 'The service period year of the core team.',
            ],
            'is_active' => [
                'description' => 'Whether this core team period is currently active.',
            ],
        ];
    }
}
