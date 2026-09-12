<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Blank optional identifiers are stored as NULL rather than '', so that
     * any number of staff can be left without an IC or contact number while
     * the unique rules below still apply to real values.
     */
    protected function prepareForValidation(): void
    {
        foreach (['ic', 'contact'] as $field) {
            if ($this->has($field) && trim((string) $this->input($field)) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    public function rules(): array
    {
        $ignore = $this->route('staff')->id;

        return [
            'staff_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:Male,Female'],
            'ic' => [
                'required', 'string', 'max:50',
                Rule::unique('staff', 'ic')->whereNull('deleted_at')->ignore($ignore, 'id'),
            ],
            'dob' => ['required', 'date'],
            'contact' => [
                'required', 'string', 'max:50',
                Rule::unique('staff', 'contact')->whereNull('deleted_at')->ignore($ignore, 'id'),
            ],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('staff', 'email')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('staff')->id, 'id'),
            ],
            'address' => ['required', 'string'],
            'postcode' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'states' => ['required', 'string', 'max:100'],
            'team_joined_date' => ['required', 'date'],
            'company_joined_date' => ['required', 'date'],
            'position_id' => ['required', 'exists:staff_position,id'],
            'team_id' => ['required', 'exists:team,id'],
            'is_active' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ];
    }
}
