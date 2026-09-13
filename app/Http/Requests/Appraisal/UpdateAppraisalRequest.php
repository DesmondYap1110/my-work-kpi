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
        // Marks are bounded by the template's own scale rather than a constant:
        // a company rating out of ten should not be told 6 is invalid. The
        // controller supplies the ceiling.
        $max = (int) $this->route('appraisal')->template->ratings->max('value') ?: 5;

        return [
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
            'review_date' => ['nullable', 'date'],
            'next_assessment_date' => ['nullable', 'date', 'after_or_equal:review_date'],
            'comments' => ['nullable', 'string', 'max:5000'],

            // Blank is meaningful - it leaves a measurement unrated, which
            // drops it from the score rather than marking it zero.
            'employee' => ['array'],
            'employee.*' => ['nullable', 'integer', 'between:1,'.$max],
            'reviewer' => ['array'],
            'reviewer.*' => ['nullable', 'integer', 'between:1,'.$max],

            'project_employee' => ['array'],
            'project_employee.*' => ['nullable', 'integer', 'between:1,'.$max],
            'project_reviewer' => ['array'],
            'project_reviewer.*' => ['nullable', 'integer', 'between:1,'.$max],
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
            'employee.*.between' => 'Every mark must be on the rating scale.',
            'reviewer.*.between' => 'Every mark must be on the rating scale.',
            'project_employee.*.between' => 'Every mark must be on the rating scale.',
            'project_reviewer.*.between' => 'Every mark must be on the rating scale.',
        ];
    }
}
