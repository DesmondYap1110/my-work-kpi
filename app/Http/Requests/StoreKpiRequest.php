<?php

namespace App\Http\Requests;

use App\Models\StaffPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKpiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position_id' => [
                'required',
                // Must be a position that has no KPI yet, and never the
                // Administrator - that one is the access gate, not a job.
                // whereNot, not where(...,'!=',...): the Exists rule's where()
                // only takes a column and a value, so an operator argument is
                // silently dropped.
                Rule::exists('staff_position', 'id')
                    ->where('has_kpi', false)
                    ->whereNot('id', StaffPosition::ADMIN_ID)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'position_id.exists' => 'That position cannot be given a KPI - it either already has one, or is the Administrator.',
        ];
    }
}
