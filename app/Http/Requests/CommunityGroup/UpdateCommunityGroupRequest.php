<?php

namespace App\Http\Requests\CommunityGroup;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunityGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'priority' => ['sometimes', 'integer', 'between:0,127'],
            'description' => ['sometimes', 'string'],
            'logo' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
