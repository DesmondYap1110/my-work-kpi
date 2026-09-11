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
        $ignore = $this->route('staff')->staff_id;

        return [
            'staff_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:Male,Female'],
            'ic' => [
                'nullable', 'string', 'max:50',
                Rule::unique('staff', 'ic')->whereNull('deleted_at')->ignore($ignore, 'staff_id'),
            ],
            'dob' => ['nullable', 'date'],
            'contact' => [
                'nullable', 'string', 'max:50',
                Rule::unique('staff', 'contact')->whereNull('deleted_at')->ignore($ignore, 'staff_id'),
            ],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('staff', 'email')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('staff')->staff_id, 'staff_id'),
            ],
            'staff_address' => ['nullable', 'string'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'states' => ['nullable', 'string', 'max:100'],
            'datejointeam' => ['nullable', 'date'],
            'datejoincompany' => ['nullable', 'date'],
            'position_id' => ['required', 'exists:staff_position,position_ID'],
            'team_id' => ['required', 'exists:team,team_id'],
            'staffstatus' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ];
    }
}
