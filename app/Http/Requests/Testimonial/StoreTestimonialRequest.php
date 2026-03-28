<?php

namespace App\Http\Requests\Testimonial;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'position' => ['required', 'string', 'max:255'],
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'content' => ['required', 'string'],
        ];
    }
}
