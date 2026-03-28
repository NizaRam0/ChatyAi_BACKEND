<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GeneratePromptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //return true;
        return auth()->check(); // Only allow authenticated users to make this request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => ['required',
             'image', 
             'mimes:jpeg,png,jpg', 
             'max:10240', // 10MB
             'min:1',//min size 1KB
             'dimensions:min_width=100,min_height=100,max_width=10000,max_height=10000'
            ]
        ];
    }
    public function messages(): array
    {
        return [
            'image.min' => 'The image must be at least :min KB.',
            'image.required' => 'The image field is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg.',
            'image.max' => 'The image may not be greater than 10MB.',
            'image.dimensions' => 'The image dimensions are invalid. Minimum: 100x100 pixels, Maximum: 10000x10000 pixels.',
        ];
    }
}
