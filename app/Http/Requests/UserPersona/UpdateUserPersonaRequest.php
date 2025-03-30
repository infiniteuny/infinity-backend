<?php

namespace App\Http\Requests\UserPersona;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPersonaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'persona_id' => ['sometimes', 'uuid', 'exists:personas,id'],
        ];
    }
}
