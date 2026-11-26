<?php

namespace App\Http\Requests\Group;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $nameRules = ['required', 'string', 'max:255'];

        if ($this->user()) {
            $guardName = $this->input('guard_name');

            if (is_string($guardName)) {
                $nameRules[] = Rule::unique('groups', 'name')->where('guard_name', $guardName);
            }
        }

        return [
            'name' => $nameRules,
            'guard_name' => ['required', 'string', 'in:api'],
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
