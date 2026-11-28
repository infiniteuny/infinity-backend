<?php

namespace App\Http\Requests\CoreTeam;

use App\Models\CoreTeam;
use App\Models\Group;
use App\Rules\NoDeactivateActive;
use App\Traits\HasBooleanInput;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\QueryException;
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
        $routeCoreTeam = $this->route('core_team');
        $coreTeam = $routeCoreTeam instanceof CoreTeam
            ? $routeCoreTeam
            : ($this->user() && is_string($routeCoreTeam) && Str::isUuid($routeCoreTeam)
                ? CoreTeam::find($routeCoreTeam)
                : null);

        $yearUniqueRule = Rule::unique('core_teams', 'year');

        if ($coreTeam?->id) {
            $yearUniqueRule->ignore($coreTeam->id);
        }

        return [
            'year' => [
                'bail',
                'sometimes',
                'integer',
                'between:1901,2155',
                $yearUniqueRule,
                static function (string $attribute, mixed $value, Closure $fail) use ($coreTeam): void {
                    if ((! is_int($value) && ! is_string($value)) || filter_var($value, FILTER_VALIDATE_INT) === false) {
                        return;
                    }

                    $year = (int) $value;
                    if ($year < 1901 || $year > 2155) {
                        return;
                    }

                    try {
                        $query = Group::query()
                            ->where('name', 'Core Team '.$year)
                            ->where('guard_name', 'api');

                        if ($coreTeam instanceof CoreTeam && is_string($coreTeam->group_id) && $coreTeam->group_id !== '') {
                            $query->where('id', '!=', $coreTeam->group_id);
                        }

                        $exists = $query->exists();
                    } catch (QueryException) {
                        return;
                    }

                    if ($exists) {
                        $fail('The group name for this year is already taken.');
                    }
                },
            ],
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
