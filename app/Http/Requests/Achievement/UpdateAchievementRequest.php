<?php

namespace App\Http\Requests\Achievement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAchievementRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'team_id' => ['sometimes', 'uuid', 'exists:teams,id'],
            'competition_id' => ['sometimes', 'uuid', 'exists:competitions,id'],
            'competition_team_type_id' => ['sometimes', 'uuid', 'exists:competition_team_types,id'],
            'competition_scale_id' => ['sometimes', 'uuid', 'exists:competition_scales,id'],
            'competition_time_range_id' => ['sometimes', 'uuid', 'exists:competition_time_ranges,id'],
            'competition_output_id' => ['sometimes', 'uuid', 'exists:competition_outputs,id'],
            'competition_rank_id' => ['sometimes', 'uuid', 'exists:competition_ranks,id'],
            'competition_branch' => ['sometimes', 'string'],
            'competition_start_date' => ['sometimes', 'date', 'before_or_equal:competition_end_date'],
            'competition_end_date' => ['sometimes', 'date', 'after_or_equal:competition_start_date'],
            'description' => ['sometimes', 'string'],
            'image' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['sometimes', 'string', 'in:PENDING,REJECTED,ACCEPTED'],
        ];
    }
}
