<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The table form marks that the positions field was on it, because
     * ticking nothing sends no array at all - and nothing ticked must still
     * mean "every position", not "leave as it was".
     */
    protected function prepareForValidation(): void
    {
        if ($this->boolean('position_ids_present') && ! $this->has('position_ids')) {
            $this->merge(['position_ids' => []]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('project_tag', 'name')->whereNull('deleted_at')->ignore($this->route('project_tag')?->id),
            ],
            // Fractional on purpose - a small task is worth 0.2.
            'points' => ['required', 'numeric', 'min:0', 'max:9999'],
            // The positions the tag is for. None: every position.
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'distinct', Rule::exists('staff_position', 'id')->whereNull('deleted_at')],
        ];
    }
}
