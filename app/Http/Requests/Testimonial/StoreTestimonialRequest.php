<?php

namespace App\Http\Requests\Testimonial;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'position' => 'required|regex:/^[a-zA-Z.0-9.\s]+$/|max:255',
            'photo' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'content' => 'required|string',
        ];
    }
}
