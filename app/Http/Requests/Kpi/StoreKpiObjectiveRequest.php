<?php

namespace App\Http\Requests\Kpi;

use App\Http\Requests\Concerns\ScopesCategoryToPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKpiObjectiveRequest extends FormRequest
{
    use ScopesCategoryToPosition;

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

            // One of this position's categories, or the sentinel + a name.
            // Required: the tree is built category-first, so an objective
            // with nowhere to sit would simply not be displayed.
            'category_id' => [
                'required',
                Rule::when(
                    filled($this->input('category_id')) && $this->input('category_id') !== self::NEW,
                    [$this->categoryBelongsToPosition()]
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
