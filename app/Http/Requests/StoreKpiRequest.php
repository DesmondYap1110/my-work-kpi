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
            'kpi_title' => ['required', 'string', 'max:255'],
            'position_ID' => [
                'required',
                Rule::exists('staff_position', 'position_ID')->where('kpistatus', false)->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'position_ID.exists' => 'The selected position already has a KPI template assigned.',
        ];
    }
}
