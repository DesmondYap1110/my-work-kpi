<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKpiObjectiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'obj_type' => ['required', 'in:0,1'],
            'objmk_2' => ['nullable', 'boolean'],
            'objmk_1' => ['nullable', 'boolean'],
            'objmk_0' => ['nullable', 'boolean'],
            'objmk_n1' => ['nullable', 'boolean'],
            'objmk_n2' => ['nullable', 'boolean'],
        ];
    }
}
