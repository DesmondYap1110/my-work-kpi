<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProjectPhaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:project,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:0,1'],
            'start_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:start_date'],
            'remark_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'invoice_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $project = Project::find($this->input('project_id'));

            if (! $project) {
                return;
            }

            if ($this->filled('start_date') && $this->date('start_date')->lt($project->start_date)) {
                $validator->errors()->add('start_date', 'Phase start date cannot be before the project start date.');
            }

            if ($this->filled('due_date') && $this->date('due_date')->gt($project->end_date)) {
                $validator->errors()->add('due_date', 'Phase due date cannot be after the project end date.');
            }
        });
    }
}
