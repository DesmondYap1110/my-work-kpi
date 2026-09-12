<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKpiObjectiveRequest extends FormRequest
{
    /**
     * Sentinel posted by the "+ Add new" option on a select - see
     * public/js/modules/select-or-new.js.
     */
    public const NEW = '__new__';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            // Either an existing category, nothing, or the sentinel + a name.
            'category_id' => [
                'nullable',
                Rule::when(
                    filled($this->input('category_id')) && $this->input('category_id') !== self::NEW,
                    ['exists:kpi_category,id']
                ),
            ],
            'category_name' => [
                Rule::requiredIf($this->input('category_id') === self::NEW),
                'nullable', 'string', 'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category_name.required' => 'Enter a name for the new category.',
        ];
    }
}
