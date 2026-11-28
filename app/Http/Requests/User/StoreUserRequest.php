<?php

namespace App\Http\Requests\User;

use App\Rules\Associative;
use App\Traits\HasBooleanInput;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    use HasBooleanInput;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->prepareBooleanInput(['is_member', 'is_extraordinary']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $startDateRules = ['sometimes', 'nullable', 'date'];
        $endDateRules = ['sometimes', 'nullable', 'date'];

        if (! $this->user() || $this->filled('end_date')) {
            $startDateRules[] = 'before_or_equal:end_date';
        }

        if (! $this->user() || $this->filled('start_date')) {
            $endDateRules[] = 'after_or_equal:start_date';
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:150', 'unique:users,username'],
            'email_address' => ['required', 'string', 'email', 'max:255', 'unique:users,email_address'],
            'phone_number' => ['required', 'string', 'max:255', 'unique:users,phone_number'],
            'student_id' => ['required', 'string', 'max:255', 'unique:users,student_id'],
            'major_id' => ['required', 'uuid', 'exists:majors,id'],
            'links' => ['sometimes', 'nullable', new Associative],
            'links.*' => ['string', 'max:2048'],
            'start_date' => $startDateRules,
            'end_date' => $endDateRules,
            'is_member' => ['required', 'boolean'],
            'is_extraordinary' => ['required', 'boolean'],
        ];

        if ($this->user()?->cannot('manage-user-membership')) {
            unset(
                $rules['start_date'],
                $rules['end_date'],
                $rules['is_member'],
                $rules['is_extraordinary'],
            );
        }

        return $rules;
    }

    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();

        if ($this->user()?->cannot('manage-user-membership')) {
            unset(
                $validated['start_date'],
                $validated['end_date'],
                $validated['is_member'],
                $validated['is_extraordinary'],
            );
        }

        return data_get($validated, $key, $default);
    }

    /**
     * Get the body parameters for API documentation.
     *
     * @return array<string, array{description: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The full name of the user.',
            ],
            'username' => [
                'description' => 'The unique username of the user.',
            ],
            'email_address' => [
                'description' => 'The unique email address of the user.',
            ],
            'phone_number' => [
                'description' => 'The unique phone number of the user.',
            ],
            'student_id' => [
                'description' => 'The unique student ID number of the user.',
            ],
            'major_id' => [
                'description' => 'The ID of the major the user belongs to.',
            ],
            'start_date' => [
                'description' => 'The membership start date of the user.',
            ],
            'end_date' => [
                'description' => 'The membership end date of the user.',
            ],
            'is_member' => [
                'description' => 'Whether the user is a registered member.',
            ],
            'is_extraordinary' => [
                'description' => 'Whether the user is an extraordinary member.',
            ],
        ];
    }
}
