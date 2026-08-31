<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProjectPhaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'p_PTitle' => ['required', 'string', 'max:255'],
            'p_Type' => ['required', 'in:0,1'],
            'p_SDate' => ['required', 'date'],
            'p_DDate' => ['required', 'date', 'after_or_equal:p_SDate'],
            'p_Remark' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'p_Invoice' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var \App\Models\ProjectPhase $phase */
            $phase = $this->route('project_phase');
            $project = $phase->project;

            if (! $project) {
                return;
            }

            if ($this->filled('p_SDate') && $this->date('p_SDate')->lt($project->p_SDate)) {
                $validator->errors()->add('p_SDate', 'Phase start date cannot be before the project start date.');
            }

            if ($this->filled('p_DDate') && $this->date('p_DDate')->gt($project->p_EDate)) {
                $validator->errors()->add('p_DDate', 'Phase due date cannot be after the project end date.');
            }
        });
    }
}
