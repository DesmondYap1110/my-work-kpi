<?php

namespace App\Http\Requests;

use App\Models\StaffPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKpiCategoryRequest extends FormRequest
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
                Rule::exists('staff_position', 'id')
                    ->whereNot('id', StaffPosition::ADMIN_ID)
                    ->whereNull('deleted_at'),
            ],
            // Unique within the position, not globally: two positions are both
            // entitled to a category called "Technical Skill".
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('kpi_category', 'name')
                    ->where('position_id', $this->input('position_id'))
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'This position already has a category with that name.',
        ];
    }
}
