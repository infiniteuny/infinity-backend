<?php

namespace App\Http\Requests\CommunityGroup;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityGroupRequest extends FormRequest
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
            'description' => ['required', 'string'],
            'logo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
