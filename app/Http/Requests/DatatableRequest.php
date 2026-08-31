<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DatatableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->ajax() || app()->environment('local', 'testing');
    }

    public function rules(): array
    {
        return [
            'class' => ['required', 'string', Rule::in(config('datatables.classes'))],
        ];
    }
}
