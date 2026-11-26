<?php

namespace App\Http\Requests\FundApplication;

use App\Models\Team;
use App\Rules\OwnedEntity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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

        $statusValues = $this->user()?->can('approve-fund-application')
            ? 'PENDING,REJECTED,ACCEPTED'
            : 'PENDING';

        return [
            'team_id' => $teamIdRules,
            'competition_instance_id' => ['sometimes', 'uuid', 'exists:competition_instances,id'],
            'competition_scale_id' => ['sometimes', 'uuid', 'exists:competition_scales,id'],
            'competition_branch' => ['sometimes', 'string'],
            'competition_start_date' => ['sometimes', 'date', 'before_or_equal:competition_end_date'],
            'competition_end_date' => ['sometimes', 'date', 'after_or_equal:competition_start_date'],
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
