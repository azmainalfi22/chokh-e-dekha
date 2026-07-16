<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // ✅ Ensure user is authenticated and NOT an admin
        return auth()->check() && !auth()->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // ✅ Required fields with proper constraints
            'title' => [
                'required',
                'string',
                'min:10',  // At least 10 characters for meaningful title
                'max:255',
            ],
            'description' => [
                'required',
                'string',
                'min:20',  // At least 20 characters for proper description
                'max:5000',
            ],
            'category' => [
                'required',
                'string',
                Rule::in([
                    'infrastructure',
                    'sanitation',
                    'safety',
                    'corruption',
                    'environment',
                    'health',
                    'traffic',
                    'noise',
                    'other',
                ]),
            ],
            'city_corporation' => [
                'required',
                'string',
                'max:100',
            ],
            'location' => [
                'required',
                'string',
                'max:500',
            ],

            // ✅ Google Maps fields (optional but validated if present)
            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'place_id' => [
                'nullable',
                'string',
                'max:255',
            ],
            'formatted_address' => [
                'nullable',
                'string',
                'max:500',
            ],

            // ✅ Media uploads with proper validation
            'photo' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120',  // 5MB max
            ],
            'photos' => [
                'nullable',
                'array',
                'max:5',  // Maximum 5 files
            ],
            'photos.*' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,webp,gif,mp4,mov',
                'max:10240',  // 10MB max per file
            ],
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for your report.',
            'title.min' => 'Title must be at least 10 characters long.',
            'description.required' => 'Please describe the issue in detail.',
            'description.min' => 'Description must be at least 20 characters long.',
            'category.required' => 'Please select a category.',
            'category.in' => 'Invalid category selected.',
            'city_corporation.required' => 'Please select your city corporation.',
            'location.required' => 'Please provide a location.',
            'photo.image' => 'The uploaded file must be an image.',
            'photo.max' => 'Image size cannot exceed 5MB.',
            'photos.max' => 'You can upload a maximum of 5 files.',
            'photos.*.max' => 'Each file cannot exceed 10MB.',
        ];
    }

    /**
     * Get custom attribute names for better error messages
     */
    public function attributes(): array
    {
        return [
            'city_corporation' => 'city',
            'formatted_address' => 'address',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        // ✅ Sanitize inputs
        $this->merge([
            'title' => $this->input('title') ? trim($this->input('title')) : null,
            'description' => $this->input('description') ? trim($this->input('description')) : null,
            'location' => $this->input('location') ? trim($this->input('location')) : null,
        ]);
    }
}

