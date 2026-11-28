<?php

namespace App\Http\Requests\FundApplication;

use App\Models\FundApplication;
use App\Models\Team;
use App\Rules\OwnedEntity;
use DateTimeInterface;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateFundApplicationRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->user()?->cannot('approve-fund-application') && ! $this->has('status')) {
            $this->merge([
                'status' => 'PENDING',
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $teamIdRules = ['sometimes', 'uuid', 'exists:teams,id'];

        if ($this->user()?->cannot('update-fund-application')) {
            $teamIdRules[] = new OwnedEntity(Team::class, $this->user()->id);
        }

        $startDateRules = ['sometimes', 'date'];
        $endDateRules = ['sometimes', 'date'];

        if (! $this->user()) {
            $startDateRules[] = 'before_or_equal:competition_end_date';
            $endDateRules[] = 'after_or_equal:competition_start_date';
        } else {
            $routeFundApplication = $this->route('fund_application');
            $fundApplication = $routeFundApplication instanceof FundApplication
                ? $routeFundApplication
                : (is_string($routeFundApplication) && Str::isUuid($routeFundApplication)
                    ? FundApplication::find($routeFundApplication)
                    : null);

            if ($this->has('competition_end_date')) {
                if ($this->filled('competition_end_date')) {
                    $startDateRules[] = 'before_or_equal:competition_end_date';
                }
            } elseif ($fundApplication?->competition_end_date) {
                $endDate = $fundApplication->competition_end_date instanceof DateTimeInterface
                    ? $fundApplication->competition_end_date->format('Y-m-d')
                    : $fundApplication->competition_end_date;
                $startDateRules[] = 'before_or_equal:'.$endDate;
            }

            if ($this->has('competition_start_date')) {
                if ($this->filled('competition_start_date')) {
                    $endDateRules[] = 'after_or_equal:competition_start_date';
                }
            } elseif ($fundApplication?->competition_start_date) {
                $startDate = $fundApplication->competition_start_date instanceof DateTimeInterface
                    ? $fundApplication->competition_start_date->format('Y-m-d')
                    : $fundApplication->competition_start_date;
                $endDateRules[] = 'after_or_equal:'.$startDate;
            }
        }

        $statusValues = $this->user()?->cannot('approve-fund-application')
            ? 'PENDING'
            : 'PENDING,REJECTED,ACCEPTED';

        return [
            'team_id' => $teamIdRules,
            'competition_instance_id' => ['sometimes', 'uuid', 'exists:competition_instances,id'],
            'competition_scale_id' => ['sometimes', 'uuid', 'exists:competition_scales,id'],
            'competition_branch' => ['sometimes', 'string', 'max:255'],
            'competition_start_date' => $startDateRules,
            'competition_end_date' => $endDateRules,
            'letter_of_acceptance' => ['sometimes', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:20480'],
            'proposal' => ['sometimes', 'file', 'mimes:doc,docx', 'max:20480'],
            'status' => ['sometimes', 'string', 'in:'.$statusValues],
        ];
    }

    /**
     * Get the body parameters for API documentation.
     *
     * @return array<string, array{description: string}>
     */
    public function bodyParameters(): array
    {
        return [
            'team_id' => [
                'description' => 'The ID of the team submitting the fund application.',
            ],
            'competition_instance_id' => [
                'description' => 'The ID of the competition instance the funding is requested for.',
            ],
            'competition_scale_id' => [
                'description' => 'The ID of the competition scale.',
            ],
            'competition_branch' => [
                'description' => 'The branch or category of the competition.',
            ],
            'competition_start_date' => [
                'description' => 'The start date of the competition.',
            ],
            'competition_end_date' => [
                'description' => 'The end date of the competition.',
            ],
            'letter_of_acceptance' => [
                'description' => 'The letter of acceptance document for the competition.',
            ],
            'proposal' => [
                'description' => 'The funding proposal document.',
            ],
            'status' => [
                'description' => 'The approval status of the fund application.',
            ],
        ];
    }
}
