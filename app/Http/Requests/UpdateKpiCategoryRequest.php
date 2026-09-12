<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKpiCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            // Renaming only - a category does not move between positions.
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('kpi_category', 'name')
                    ->where('position_id', $category?->position_id)
                    ->whereNull('deleted_at')
                    ->ignore($category?->id),
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
