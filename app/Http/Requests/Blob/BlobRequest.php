<?php

namespace App\Http\Requests\Blob;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BlobRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('force_download')) {
            $this->merge([
                'force_download' => filter_var($this->input('force_download'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'force_download' => ['sometimes', 'boolean'],
            'name' => ['sometimes', 'string'],
        ];
    }
}
