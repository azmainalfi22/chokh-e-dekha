<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'body' => [
                'required',
                'string',
                'min:1',
                'max:1000',
            ],
            'parent_id' => [
                'nullable',
                'exists:report_comments,id'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Comment cannot be empty.',
            'body.min' => 'Comment must be at least 1 character.',
            'body.max' => 'Comment cannot exceed 1000 characters.',
            'parent_id.exists' => 'Invalid parent comment.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'body' => trim($this->body ?? ''),
        ]);
    }
}