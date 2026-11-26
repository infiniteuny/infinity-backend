<?php

namespace App\Http\Requests\FundApplication;

use App\Models\Team;
use App\Rules\OwnedEntity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFundApplicationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $teamIdRules = ['required', 'uuid', 'exists:teams,id'];

        if ($this->user()?->cannot('create-fund-application')) {
            $teamIdRules[] = new OwnedEntity(Team::class, $this->user()->id);
        }

        $statusValues = $this->user()?->cannot('approve-fund-application')
            ? 'PENDING'
            : 'PENDING,REJECTED,ACCEPTED';

        return [
            'team_id' => $teamIdRules,
            'competition_instance_id' => ['required', 'uuid', 'exists:competition_instances,id'],
            'competition_scale_id' => ['required', 'uuid', 'exists:competition_scales,id'],
            'competition_branch' => ['required', 'string'],
            'competition_start_date' => ['required', 'date', 'before_or_equal:competition_end_date'],
            'competition_end_date' => ['required', 'date', 'after_or_equal:competition_start_date'],
            'letter_of_acceptance' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:20480'],
            'proposal' => ['required', 'file', 'mimes:doc,docx', 'max:20480'],
            'status' => ['required', 'string', 'in:'.$statusValues],
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
