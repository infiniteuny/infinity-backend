<?php

namespace App\Http\Requests\User;

use App\Models\User;
use App\Rules\Associative;
use App\Traits\HasBooleanInput;
use DateTimeInterface;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $routeUser = $this->route('user');
        $ignoreUser = $routeUser;

        $startDateRules = ['sometimes', 'nullable', 'date'];
        $endDateRules = ['sometimes', 'nullable', 'date'];

        if (! $this->user()) {
            $startDateRules[] = 'before_or_equal:end_date';
            $endDateRules[] = 'after_or_equal:start_date';
        } else {
            $user = $routeUser instanceof User
                ? $routeUser
                : (is_string($routeUser) && Str::isUuid($routeUser)
                    ? User::find($routeUser)
                    : null);
            $ignoreUser = $user ?? $routeUser;

            if ($this->has('end_date')) {
                if ($this->filled('end_date')) {
                    $startDateRules[] = 'before_or_equal:end_date';
                }
            } elseif ($user?->end_date) {
                $endDate = $user->end_date instanceof DateTimeInterface
                    ? $user->end_date->format('Y-m-d')
                    : $user->end_date;
                $startDateRules[] = 'before_or_equal:'.$endDate;
            }

            if ($this->has('start_date')) {
                if ($this->filled('start_date')) {
                    $endDateRules[] = 'after_or_equal:start_date';
                }
            } elseif ($user?->start_date) {
                $startDate = $user->start_date instanceof DateTimeInterface
                    ? $user->start_date->format('Y-m-d')
                    : $user->start_date;
                $endDateRules[] = 'after_or_equal:'.$startDate;
            }
        }

        $rules = [
            'name' => ['sometimes', 'string'],
            'username' => ['sometimes', 'string', Rule::unique('users', 'username')->ignore($ignoreUser)],
            'email_address' => ['sometimes', 'email', Rule::unique('users', 'email_address')->ignore($ignoreUser)],
            'phone_number' => ['sometimes', 'string', Rule::unique('users', 'phone_number')->ignore($ignoreUser)],
            'student_id' => ['sometimes', 'string', Rule::unique('users', 'student_id')->ignore($ignoreUser)],
            'major_id' => ['sometimes', 'uuid', 'exists:majors,id'],
            'links' => ['sometimes', 'nullable', new Associative],
            'links.*' => ['string'],
            'start_date' => $startDateRules,
            'end_date' => $endDateRules,
            'is_member' => ['sometimes', 'boolean'],
            'is_extraordinary' => ['sometimes', 'boolean'],
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
