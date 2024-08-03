<?php

namespace App\Http\Requests\Testimonial;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonialRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'position' => ['sometimes', 'string', 'max:255'],
            'photo' => ['sometimes', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'content' => ['sometimes', 'string'],
        ];
    }
}
