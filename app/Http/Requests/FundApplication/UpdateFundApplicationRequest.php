<?php

namespace App\Http\Requests\FundApplication;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFundApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'team_id' => 'required|exists:teams,id',
            'competition_id' => 'required|exists:competitions,id',
            'competition_team_type_id' => 'required|exists:competition_team_types,id',
            'competition_scale_id' => 'required|exists:competition_scales,id',
            'competition_branch' => 'required|string|max:255',
            'competition_date' => 'required|date',
            'letter_of_acceptance' => 'required|string|max:255',
            'proposal' => 'required|string|max:255',
            'status' => 'in:pending,rejected,accepted',
        ];
    }
}
