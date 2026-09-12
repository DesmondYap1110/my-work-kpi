<?php

namespace App\Http\Requests;

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
            'added_date' => ['required', 'date'],
            'start_date' => ['required', 'date', 'after_or_equal:added_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
