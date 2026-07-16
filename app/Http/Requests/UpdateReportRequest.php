<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // ✅ Only admins can update reports
        return auth()->check() && auth()->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['pending', 'in_progress', 'resolved', 'rejected']),
            ],
            'admin_note' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'priority' => [
                'nullable',
                'string',
                Rule::in(['low', 'normal', 'high', 'urgent']),
            ],
            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            // Location corrections
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
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'priority.in' => 'Invalid priority level.',
            'assigned_to.exists' => 'Selected user does not exist.',
        ];
    }
}

