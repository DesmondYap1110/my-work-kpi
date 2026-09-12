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
                // Any real position except the Administrator - that one is the
                // access gate, not a job. Whether it already has objectives is
                // not checked here: "assign" just opens the objectives page,
                // so landing on one that is already started is harmless.
                //
                // whereNot, not where(...,'!=',...): the Exists rule's where()
                // only takes a column and a value, so an operator argument is
                // silently dropped.
                Rule::exists('staff_position', 'id')
                    ->whereNot('id', StaffPosition::ADMIN_ID)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'position_id.exists' => 'That position cannot be given a KPI - the Administrator position is not assessed.',
        ];
    }
}
