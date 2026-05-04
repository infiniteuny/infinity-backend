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
        $rules = [
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

        if (! $this->user()->can('manage-user-membership')) {
            unset(
                $rules['start_date'],
                $rules['end_date'],
                $rules['is_member'],
                $rules['is_extraordinary'],
            );
        }

        return $rules;
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        if (! $this->user()->can('manage-user-membership')) {
            unset(
                $validated['start_date'],
                $validated['end_date'],
                $validated['is_member'],
                $validated['is_extraordinary'],
            );
        }

        return $validated;
    }
}
