<?php

namespace App\Http\Requests\Testimonial;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonialRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'position' => ['sometimes', 'string', 'max:255'],
            'photo' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'content' => ['sometimes', 'string'],
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
            'name' => [
                'description' => 'The name of the person giving the testimonial.',
            ],
            'position' => [
                'description' => 'The position or role of the person giving the testimonial.',
            ],
            'photo' => [
                'description' => 'The photo of the person giving the testimonial.',
            ],
            'content' => [
                'description' => 'The content of the testimonial.',
            ],
        ];
    }
}
