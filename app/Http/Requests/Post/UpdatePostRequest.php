<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'content' => 'required|string',
            'time' => 'required|date_format:H:i:s',
            'cover_image' => 'image|mimes:jpg,png,jpeg|max:2048',
        ];
    }
}
