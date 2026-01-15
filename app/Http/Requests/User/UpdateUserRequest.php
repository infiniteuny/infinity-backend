<?php

namespace App\Http\Requests\User;

use App\Rules\Associative;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['sometimes', 'string'],
            'email_address' => ['sometimes', 'email', Rule::unique('users', 'email_address')->ignore($user)],
            'phone_number' => ['sometimes', 'string', Rule::unique('users', 'phone_number')->ignore($user)],
            'student_id' => ['sometimes', 'string', Rule::unique('users', 'student_id')->ignore($user)],
            'major_id' => ['sometimes', 'uuid', 'exists:majors,id'],
            'links' => ['sometimes', new Associative],
            'links.*' => ['sometimes', 'url'],
            'start_date' => ['sometimes', 'nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'is_member' => ['sometimes', 'boolean'],
            'is_extraordinary' => ['sometimes', 'boolean'],
        ];
    }
}
