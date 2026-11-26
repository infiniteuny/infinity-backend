<?php

namespace App\Http\Requests\UserPersona;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreUserPersonaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $personaIdRules = ['required', 'uuid', 'exists:personas,id'];

        if ($this->user()) {
            $routeUser = $this->route('user');
            $userId = $routeUser instanceof User
                ? $routeUser->id
                : $routeUser;
            $personaId = $this->input('persona_id');

            if (
                is_string($userId) && Str::isUuid($userId) &&
                is_string($personaId) && Str::isUuid($personaId)
            ) {
                $personaIdRules[] = Rule::unique('user_personas', 'persona_id')
                    ->where('user_id', $userId);
            }
        }

        return [
            'persona_id' => $personaIdRules,
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
            'persona_id' => [
                'description' => 'The ID of the persona to assign to the user.',
            ],
        ];
    }
}
