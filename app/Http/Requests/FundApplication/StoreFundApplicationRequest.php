<?php

namespace App\Http\Requests\FundApplication;

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
        return [
            'team_id' => ['required', 'uuid', 'exists:teams,id'],
            'competition_instance_id' => ['required', 'uuid', 'exists:competition_instances,id'],
            'competition_scale_id' => ['required', 'uuid', 'exists:competition_scales,id'],
            'competition_branch' => ['required', 'string'],
            'competition_start_date' => ['required', 'date', 'before_or_equal:competition_end_date'],
            'competition_end_date' => ['required', 'date', 'after_or_equal:competition_start_date'],
            'letter_of_acceptance' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:20480'],
            'proposal' => ['required', 'file', 'mimes:doc,docx', 'max:20480'],
            'status' => ['required', 'string', 'in:PENDING,REJECTED,ACCEPTED'],
        ];
    }
}
