<?php

namespace App\Http\Requests\FundApplication;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFundApplicationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'team_id' => ['sometimes', 'exists:teams,id'],
            'competition_id' => ['sometimes', 'exists:competitions,id'],
            'competition_team_type_id' => ['sometimes', 'exists:competition_team_types,id'],
            'competition_scale_id' => ['sometimes', 'exists:competition_scales,id'],
            'competition_branch' => ['sometimes', 'string'],
            'competition_start_date' => ['sometimes', 'date', 'before_or_equal:competition_end_date'],
            'competition_end_date' => ['sometimes', 'date', 'after_or_equal:competition_start_date'],
            'letter_of_acceptance' => ['sometimes', 'mimes:pdf,jpg,jpeg,png,webp', 'max:20480'],
            'proposal' => ['sometimes', 'mimes:doc,docx', 'max:20480'],
            'status' => ['sometimes', 'string', 'in:PENDING,REJECTED,ACCEPTED'],
        ];
    }
}
