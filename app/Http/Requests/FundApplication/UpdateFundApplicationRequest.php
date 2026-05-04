<?php

namespace App\Http\Requests\FundApplication;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFundApplicationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $statusValues = $this->user()?->can('approve-fund-application')
            ? 'PENDING,REJECTED,ACCEPTED'
            : 'PENDING';

        return [
            'team_id' => ['sometimes', 'uuid', 'exists:teams,id'],
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
}
