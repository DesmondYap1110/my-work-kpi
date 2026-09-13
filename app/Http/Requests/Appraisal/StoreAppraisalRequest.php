<?php

namespace App\Http\Requests\Appraisal;

use App\Models\StaffPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppraisalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // The administrator is the appraiser, never the appraised.
            'staff_id' => ['required', 'integer', Rule::exists('staff', 'id')
                ->where('is_active', true)
                ->whereNot('position_id', StaffPosition::ADMIN_ID)
                ->whereNull('deleted_at')],
            // The period is the point of the thing: it decides which project
            // work Part 2 is drawn from.
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
            'review_date' => ['nullable', 'date'],
            'next_assessment_date' => ['nullable', 'date', 'after_or_equal:review_date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'staff_id' => 'member',
            'period_from' => 'review period start',
            'period_to' => 'review period end',
            'next_assessment_date' => 'next assessment date',
        ];
    }
}
