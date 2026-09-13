<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // No team: a project belongs to the people with tasks on it, and those
        // are chosen task by task once it exists.
        return [
            'title' => ['required', 'string', 'max:255'],
            // No "added date": created_at already records when the project was
            // entered, so start_date has no floor - a project may have started
            // before anyone got round to recording it.
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
