<?php

namespace App\Http\Requests;

use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'p_Title' => ['required', 'string', 'max:255'],
            'p_addDate' => ['required', 'date'],
            'p_SDate' => ['required', 'date', 'after_or_equal:p_addDate'],
            'p_EDate' => ['required', 'date', 'after_or_equal:p_SDate'],
            'team_id' => ['required', 'exists:team,team_id'],
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
