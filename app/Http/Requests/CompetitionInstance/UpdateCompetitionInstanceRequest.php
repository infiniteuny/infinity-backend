<?php

namespace App\Http\Requests\CompetitionInstance;

use App\Models\CompetitionInstance;
use DateTimeInterface;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateCompetitionInstanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $startDateRules = ['sometimes', 'date'];
        $endDateRules = ['sometimes', 'date'];

        if (! $this->user()) {
            $startDateRules[] = 'before_or_equal:end_date';
            $endDateRules[] = 'after_or_equal:start_date';
        } else {
            $routeCompetitionInstance = $this->route('competition_instance');
            $competitionInstance = $routeCompetitionInstance instanceof CompetitionInstance
                ? $routeCompetitionInstance
                : (is_string($routeCompetitionInstance) && Str::isUuid($routeCompetitionInstance)
                    ? CompetitionInstance::find($routeCompetitionInstance)
                    : null);

            if ($this->has('end_date')) {
                if ($this->filled('end_date')) {
                    $startDateRules[] = 'before_or_equal:end_date';
                }
            } elseif ($competitionInstance?->end_date) {
                $endDate = $competitionInstance->end_date instanceof DateTimeInterface
                    ? $competitionInstance->end_date->format('Y-m-d')
                    : $competitionInstance->end_date;
                $startDateRules[] = 'before_or_equal:'.$endDate;
            }

            if ($this->has('start_date')) {
                if ($this->filled('start_date')) {
                    $endDateRules[] = 'after_or_equal:start_date';
                }
            } elseif ($competitionInstance?->start_date) {
                $startDate = $competitionInstance->start_date instanceof DateTimeInterface
                    ? $competitionInstance->start_date->format('Y-m-d')
                    : $competitionInstance->start_date;
                $endDateRules[] = 'after_or_equal:'.$startDate;
            }
        }

        return [
            'competition_id' => ['sometimes', 'uuid', 'exists:competitions,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'shortname' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'url' => ['sometimes', 'nullable', 'string', 'url:http,https'],
            'organizer' => ['sometimes', 'string', 'max:255'],
            'organizer_type_id' => ['sometimes', 'uuid', 'exists:competition_organizer_types,id'],
            'logo' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'start_date' => $startDateRules,
            'end_date' => $endDateRules,
            'location' => ['sometimes', 'string', 'max:255'],
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
            'competition_id' => [
                'description' => 'The ID of the parent competition series.',
            ],
            'name' => [
                'description' => 'The name of the competition instance.',
            ],
            'shortname' => [
                'description' => 'The short name or acronym of the competition instance.',
            ],
            'description' => [
                'description' => 'The description of the competition instance.',
            ],
            'url' => [
                'description' => 'The official website URL of the competition instance.',
            ],
            'organizer' => [
                'description' => 'The name of the competition organizer.',
            ],
            'organizer_type_id' => [
                'description' => 'The ID of the competition organizer type.',
            ],
            'logo' => [
                'description' => 'The logo image of the competition instance.',
            ],
            'start_date' => [
                'description' => 'The start date of the competition instance.',
            ],
            'end_date' => [
                'description' => 'The end date of the competition instance.',
            ],
            'location' => [
                'description' => 'The location or venue of the competition instance.',
            ],
        ];
    }
}
