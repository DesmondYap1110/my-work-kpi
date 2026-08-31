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

    public function rules(): array
    {
        return [
            'staff_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:Male,Female'],
            'ic' => ['nullable', 'string', 'max:50'],
            'dob' => ['nullable', 'date'],
            'contact' => ['nullable', 'string', 'max:50'],
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
