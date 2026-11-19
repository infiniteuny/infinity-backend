<?php

namespace App\Http\Requests\ProjectGallery;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectGalleryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'url' => ['sometimes', 'string', 'url:http,https'],
            'image' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
            'title' => [
                'description' => 'The title of the project.',
            ],
            'description' => [
                'description' => 'The description of the project.',
            ],
            'url' => [
                'description' => 'The URL linking to the project.',
            ],
            'image' => [
                'description' => 'The preview image of the project.',
            ],
        ];
    }
}
