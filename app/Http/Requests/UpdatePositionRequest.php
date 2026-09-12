<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position_name' => [
                'required', 'string', 'max:255',
                Rule::unique('staff_position', 'position_name')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('position')->id, 'id'),
            ],
            'job_scope' => ['nullable', 'string'],
        ];
    }
}
