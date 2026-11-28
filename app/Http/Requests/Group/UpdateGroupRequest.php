<?php

namespace App\Http\Requests\Group;

use App\Models\Group;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $nameRules = [
            'sometimes',
            'string',
            'max:255',
            Rule::notRegex('/^\s*(Core\s+Team(\s+\d{4})?|Ex\s+Core\s+Team|CG\s+Admin(\s+\d{4})?|Ex\s+CG\s+Admin|Active\s+Member|Inactive\s+Member|Member|Administrator)\s*$/iu'),
        ];
        $guardNameRules = ['sometimes', 'string', 'in:api'];

        if ($this->user()) {
            $routeGroup = $this->route('group');
            $group = $routeGroup instanceof Group
                ? $routeGroup
                : (is_string($routeGroup) && Str::isUuid($routeGroup)
                    ? Group::find($routeGroup)
                    : null);
            $ignoreGroup = $group ?? $routeGroup;

            $effectiveGuardName = $this->has('guard_name')
                ? $this->input('guard_name')
                : ($group?->guard_name ?? 'api');
            $effectiveName = $this->has('name')
                ? $this->input('name')
                : $group?->name;

            if ($this->has('name') && is_string($effectiveGuardName)) {
                $nameRules[] = Rule::unique('groups', 'name')
                    ->where('guard_name', $effectiveGuardName)
                    ->ignore($ignoreGroup);
            } elseif ($this->has('guard_name') && is_string($effectiveName)) {
                $guardNameRules[] = Rule::unique('groups', 'guard_name')
                    ->where('name', $effectiveName)
                    ->ignore($ignoreGroup);
            }
        }

        return [
            'name' => $nameRules,
            'guard_name' => $guardNameRules,
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
            'name' => [
                'description' => 'The name of the group.',
            ],
            'guard_name' => [
                'description' => 'The authentication guard name for the group.',
            ],
        ];
    }
}
