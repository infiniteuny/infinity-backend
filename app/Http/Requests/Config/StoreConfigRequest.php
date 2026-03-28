<?php

namespace App\Http\Requests\Config;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreConfigRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string'],
            'value' => ['required', 'string'],
            'type' => ['required', 'string', 'in:STRING,INTEGER,BOOLEAN'],
            'is_private' => ['required', 'boolean'],
        ];
    }
}
