<?php

namespace App\Http\Requests\Achievement;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAchievementRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $statusValues = $this->user()->can('approve-achievement')
            ? 'PENDING,REJECTED,ACCEPTED'
            : 'PENDING';

        return [
            'team_id' => ['required', 'uuid', 'exists:teams,id'],
            'competition_instance_id' => ['required', 'uuid', 'exists:competition_instances,id'],
            'competition_scale_id' => ['required', 'uuid', 'exists:competition_scales,id'],
            'competition_time_range_id' => ['required', 'uuid', 'exists:competition_time_ranges,id'],
            'competition_output_id' => ['required', 'uuid', 'exists:competition_outputs,id'],
            'competition_rank_id' => ['required', 'uuid', 'exists:competition_ranks,id'],
            'competition_branch' => ['required', 'string'],
            'competition_start_date' => ['required', 'date', 'before_or_equal:competition_end_date'],
            'competition_end_date' => ['required', 'date', 'after_or_equal:competition_start_date'],
            'description' => ['required', 'string'],
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['required', 'string', 'in:'.$statusValues],
        ];
    }
}
