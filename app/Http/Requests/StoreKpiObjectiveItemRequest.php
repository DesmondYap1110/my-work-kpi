<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKpiObjectiveItemRequest extends FormRequest
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
            // Marks are arbitrary values chosen per item, not a fixed scale.
            'allowed_marks' => ['required', 'array', 'min:1'],
            'allowed_marks.*' => ['integer'],
        ];
    }
}
