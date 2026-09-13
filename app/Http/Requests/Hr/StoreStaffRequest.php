<?php

namespace App\Http\Requests\Hr;

use App\Http\Requests\Hr\Concerns\ChecksPositionAcceptsMembers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends FormRequest
{
    use ChecksPositionAcceptsMembers;

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
        return [
            'staff_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:Male,Female'],
            'ic' => [
                'required', 'string', 'max:50',
                Rule::unique('staff', 'ic')->whereNull('deleted_at'),
            ],
            'dob' => ['required', 'date'],
            'contact' => [
                'required', 'string', 'max:50',
                Rule::unique('staff', 'contact')->whereNull('deleted_at'),
            ],
            // Soft-deleted staff free up their email for reuse, matching
            // the soft-delete semantics used everywhere else in this app.
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('staff', 'email')->whereNull('deleted_at'),
            ],
            'address' => ['required', 'string'],
            'postcode' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'states' => ['required', 'string', 'max:100'],
            'team_joined_date' => ['required', 'date'],
            'company_joined_date' => ['required', 'date'],
            'position_id' => ['required', 'exists:staff_position,id', $this->positionAcceptsMembers()],
            'team_id' => ['required', 'exists:team,id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            // Optional: blank means the welcome email goes out and the member
            // sets their own, which is the route that ends with nobody but
            // them knowing it.
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }
}
