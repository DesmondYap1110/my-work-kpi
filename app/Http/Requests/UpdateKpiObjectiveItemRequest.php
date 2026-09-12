<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKpiObjectiveItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'objective_type' => ['required', 'in:0,1'],
            // Marks are arbitrary values chosen per item, not a fixed scale.
            'allowed_marks' => ['required', 'array', 'min:1'],
            'allowed_marks.*' => ['integer'],
        ];
    }
}
