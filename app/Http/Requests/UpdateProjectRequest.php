<?php

namespace App\Http\Requests;

use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'added_date' => ['required', 'date'],
            'start_date' => ['required', 'date', 'after_or_equal:added_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'team_id' => ['required', 'exists:team,id'],
            'mark_complete' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $teamId = $this->input('team_id');

            if ($teamId && ! Team::find($teamId)?->hasActiveStaff()) {
                $validator->errors()->add('team_id', 'The selected team has no active members and cannot be assigned a project.');
            }
        });
    }
}
