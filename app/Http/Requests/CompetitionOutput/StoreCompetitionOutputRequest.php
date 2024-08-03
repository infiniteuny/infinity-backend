<?php

namespace App\Http\Requests\CompetitionOutput;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompetitionOutputRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'weight' => ['required', 'integer'],
        ];
    }
}
