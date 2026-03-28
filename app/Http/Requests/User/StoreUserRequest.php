<?php

namespace App\Http\Requests\User;

use App\Rules\Associative;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email_address' => ['required', 'email', 'unique:users,email_address'],
            'phone_number' => ['required', 'string', 'unique:users,phone_number'],
            'student_id' => ['required', 'string', 'unique:users,student_id'],
            'major_id' => ['required', 'uuid', 'exists:majors,id'],
            'links' => ['required', new Associative],
            'links.*' => ['required', 'url'],
            'start_date' => ['sometimes', 'nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'is_member' => ['required', 'boolean'],
            'is_extraordinary' => ['required', 'boolean'],
        ];
    }
}
