<?php

namespace App\Http\Requests\Blob;

use Illuminate\Foundation\Http\FormRequest;

class BlobRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'force_download' => ['sometimes', 'boolean'],
            'name' => ['sometimes', 'string'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'force_download' => filter_var(
                $this->force_download,
                FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE
            ),
        ]);
    }
}
