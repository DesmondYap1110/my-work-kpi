<?php

namespace App\Http\Requests\Appraisal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppraisalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
            'review_date' => ['nullable', 'date'],
            'next_assessment_date' => ['nullable', 'date', 'after_or_equal:review_date'],
            'comments' => ['nullable', 'string', 'max:5000'],

            // Blank is meaningful - it leaves a measurement unrated, which
            // drops it from the score rather than marking it zero. Whether a
            // mark is one the item allows is checked per item in the controller.
            // Reviewer only: the Employee column is filled in by the member.
            'reviewer' => ['array'],
            'reviewer.*' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'period_from' => 'review period start',
            'period_to' => 'review period end',
            'next_assessment_date' => 'next assessment date',
        ];
    }

    public function messages(): array
    {
        return [
            'reviewer.*.integer' => 'Every mark must be a whole number.',
        ];
    }
}
