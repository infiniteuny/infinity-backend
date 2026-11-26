<?php

namespace App\Http\Requests\Achievement;

use App\Models\Achievement;
use App\Models\Team;
use App\Rules\OwnedEntity;
use DateTimeInterface;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateAchievementRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->user()?->cannot('approve-achievement') && ! $this->has('status')) {
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

        if ($this->user()?->cannot('update-achievement')) {
            $teamIdRules[] = new OwnedEntity(Team::class, $this->user()->id);
        }

        $startDateRules = ['sometimes', 'date'];
        $endDateRules = ['sometimes', 'date'];

        if (! $this->user()) {
            $startDateRules[] = 'before_or_equal:competition_end_date';
            $endDateRules[] = 'after_or_equal:competition_start_date';
        } else {
            $routeAchievement = $this->route('achievement');
            $achievement = $routeAchievement instanceof Achievement
                ? $routeAchievement
                : (is_string($routeAchievement) && Str::isUuid($routeAchievement)
                    ? Achievement::find($routeAchievement)
                    : null);

            if ($this->has('competition_end_date')) {
                if ($this->filled('competition_end_date')) {
                    $startDateRules[] = 'before_or_equal:competition_end_date';
                }
            } elseif ($achievement?->competition_end_date) {
                $endDate = $achievement->competition_end_date instanceof DateTimeInterface
                    ? $achievement->competition_end_date->format('Y-m-d')
                    : $achievement->competition_end_date;
                $startDateRules[] = 'before_or_equal:'.$endDate;
            }

            if ($this->has('competition_start_date')) {
                if ($this->filled('competition_start_date')) {
                    $endDateRules[] = 'after_or_equal:competition_start_date';
                }
            } elseif ($achievement?->competition_start_date) {
                $startDate = $achievement->competition_start_date instanceof DateTimeInterface
                    ? $achievement->competition_start_date->format('Y-m-d')
                    : $achievement->competition_start_date;
                $endDateRules[] = 'after_or_equal:'.$startDate;
            }
        }

        $statusValues = $this->user()?->cannot('approve-achievement')
            ? 'PENDING'
            : 'PENDING,REJECTED,ACCEPTED';

        return [
            'team_id' => $teamIdRules,
            'competition_instance_id' => ['sometimes', 'uuid', 'exists:competition_instances,id'],
            'competition_scale_id' => ['sometimes', 'uuid', 'exists:competition_scales,id'],
            'competition_time_range_id' => ['sometimes', 'uuid', 'exists:competition_time_ranges,id'],
            'competition_output_id' => ['sometimes', 'uuid', 'exists:competition_outputs,id'],
            'competition_rank_id' => ['sometimes', 'uuid', 'exists:competition_ranks,id'],
            'competition_branch' => ['sometimes', 'string'],
            'competition_start_date' => $startDateRules,
            'competition_end_date' => $endDateRules,
            'description' => ['sometimes', 'string'],
            'image' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
                'description' => 'The ID of the team that earned the achievement.',
            ],
            'competition_instance_id' => [
                'description' => 'The ID of the competition instance where the achievement was earned.',
            ],
            'competition_scale_id' => [
                'description' => 'The ID of the competition scale.',
            ],
            'competition_time_range_id' => [
                'description' => 'The ID of the competition time range.',
            ],
            'competition_output_id' => [
                'description' => 'The ID of the competition output.',
            ],
            'competition_rank_id' => [
                'description' => 'The ID of the competition rank achieved.',
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
            'description' => [
                'description' => 'The description of the achievement.',
            ],
            'image' => [
                'description' => 'The supporting image or certificate for the achievement.',
            ],
            'status' => [
                'description' => 'The approval status of the achievement.',
            ],
        ];
    }
}
