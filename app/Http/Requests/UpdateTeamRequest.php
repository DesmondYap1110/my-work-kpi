<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'team_name' => [
                'required', 'string', 'max:255',
                Rule::unique('team', 'team_name')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('team')->team_id, 'team_id'),
            ],
        ];
    }
}
