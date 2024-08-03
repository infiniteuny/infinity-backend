<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'email_address' => ['sometimes', 'email', 'unique:users,email_address'],
            'phone_number' => ['sometimes', 'string', 'unique:users,phone_number'],
            'student_id' => ['sometimes', 'string', 'unique:users,student_id'],
            'major_id' => ['sometimes', 'exists:majors,id'],
            'links' => ['sometimes', 'array'],
            'links.*' => ['sometimes', 'json'],
            'role' => ['sometimes', 'in:ADMIN,STUDENT'],
            'start_date' => ['sometimes', 'nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'is_member' => ['sometimes', 'boolean'],
            'is_extraordinary' => ['sometimes', 'boolean'],
        ];
    }
}
