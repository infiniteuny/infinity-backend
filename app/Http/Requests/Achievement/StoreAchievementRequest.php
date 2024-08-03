<?php

namespace App\Http\Requests\Achievement;

use Illuminate\Foundation\Http\FormRequest;

class StoreAchievementRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'team_id' => ['required', 'exists:teams,id'],
            'competition_id' => ['required', 'exists:competitions,id'],
            'competition_team_type_id' => ['required', 'exists:competition_team_types,id'],
            'competition_scale_id' => ['required', 'exists:competition_scales,id'],
            'competition_time_range_id' => ['required', 'exists:competition_time_ranges,id'],
            'competition_output_id' => ['required', 'exists:competition_outputs,id'],
            'competition_rank_id' => ['required', 'exists:competition_ranks,id'],
            'competition_branch' => ['required', 'string'],
            'competition_start_date' => ['required', 'date', 'before_or_equal:competition_end_date'],
            'competition_end_date' => ['required', 'date', 'after_or_equal:competition_start_date'],
            'description' => ['required', 'string'],
            'image' => ['required', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['required', 'string', 'in:PENDING,REJECTED,ACCEPTED'],
        ];
    }
}
