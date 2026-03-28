<?php

namespace App\Http\Requests\CompetitionTimeRange;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompetitionTimeRangeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'weight' => ['required', 'integer', 'between:0,2147483647'],
        ];
    }
}
