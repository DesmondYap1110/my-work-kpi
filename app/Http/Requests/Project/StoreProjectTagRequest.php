<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('project_tag', 'name')->whereNull('deleted_at'),
            ],
            // Fractional on purpose - a small task is worth 0.2.
            'points' => ['required', 'numeric', 'min:0', 'max:9999'],
        ];
    }
}
