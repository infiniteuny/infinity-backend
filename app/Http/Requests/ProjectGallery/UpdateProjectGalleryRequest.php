<?php

namespace App\Http\Requests\ProjectGallery;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectGalleryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'description' => 'required|string',
            'url' => 'required|url',
            'image' => 'image|mimes:jpg,png,jpeg|max:2048',
        ];
    }
}
