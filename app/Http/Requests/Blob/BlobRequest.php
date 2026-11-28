<?php

namespace App\Http\Requests\Blob;

use App\Traits\HasBooleanInput;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BlobRequest extends FormRequest
{
    use HasBooleanInput;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->prepareBooleanInput(['force_download']);
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
            'name' => ['sometimes', 'nullable', 'string', 'max:255', 'regex:/^[^\x00-\x1F\x7F\/\\\\]+$/u'],
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
