<?php

namespace App\Http\Requests\CoreTeamDivision;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoreTeamDivisionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'priority' => ['required', 'integer', 'between:0,127'],
        ];
    }
}
