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

    /**
     * Get the query parameters for API documentation.
     *
     * @return array<string, array{description: string}>
     */
    public function queryParameters(): array
    {
        return [
            'force_download' => [
                'description' => 'Whether to force the browser to download the file instead of displaying it inline.',
            ],
            'name' => [
                'description' => 'The custom filename to use when serving or downloading the file.',
            ],
        ];
    }
}
