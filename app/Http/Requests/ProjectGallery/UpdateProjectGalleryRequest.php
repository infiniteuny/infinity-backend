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
}
