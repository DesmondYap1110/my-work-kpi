<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The status dropdown on a task row - see public/js/modules/status-select.js.
 * Deliberately narrow: this endpoint changes one field and nothing else.
 */
class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_column(TaskStatus::cases(), 'value'))],
        ];
    }
}
