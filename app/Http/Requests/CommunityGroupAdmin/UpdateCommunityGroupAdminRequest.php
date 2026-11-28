<?php

namespace App\Http\Requests\CommunityGroupAdmin;

use App\Models\CommunityGroupAdmin;
use App\Models\Group;
use App\Rules\NoDeactivateActive;
use App\Traits\HasBooleanInput;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCommunityGroupAdminRequest extends FormRequest
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
        $routeCommunityGroupAdmin = $this->route('community_group_admin');
        $communityGroupAdmin = $routeCommunityGroupAdmin instanceof CommunityGroupAdmin
            ? $routeCommunityGroupAdmin
            : ($this->user() && is_string($routeCommunityGroupAdmin) && Str::isUuid($routeCommunityGroupAdmin)
                ? CommunityGroupAdmin::find($routeCommunityGroupAdmin)
                : null);

        $yearUniqueRule = Rule::unique('community_group_admins', 'year');

        if ($communityGroupAdmin?->id) {
            $yearUniqueRule->ignore($communityGroupAdmin->id);
        }

        return [
            'year' => [
                'bail',
                'sometimes',
                'integer',
                'between:1901,2155',
                $yearUniqueRule,
                static function (string $attribute, mixed $value, Closure $fail) use ($communityGroupAdmin): void {
                    if ((! is_int($value) && ! is_string($value)) || filter_var($value, FILTER_VALIDATE_INT) === false) {
                        return;
                    }

                    $year = (int) $value;
                    if ($year < 1901 || $year > 2155) {
                        return;
                    }

                    try {
                        $query = Group::query()
                            ->where('name', 'CG Admin '.$year)
                            ->where('guard_name', 'api');

                        if (
                            $communityGroupAdmin instanceof CommunityGroupAdmin &&
                            is_string($communityGroupAdmin->group_id) &&
                            $communityGroupAdmin->group_id !== ''
                        ) {
                            $query->where('id', '!=', $communityGroupAdmin->group_id);
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
                new NoDeactivateActive($communityGroupAdmin->is_active ?? false),
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
                'description' => 'The service period year of the community group administration.',
            ],
            'is_active' => [
                'description' => 'Whether this community group administration period is currently active.',
            ],
        ];
    }
}
