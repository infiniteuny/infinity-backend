<?php

namespace App\Http\Requests\ProjectGallery;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectGalleryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'url' => ['required', 'string', 'url:http,https'],
            'image' => ['required', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
