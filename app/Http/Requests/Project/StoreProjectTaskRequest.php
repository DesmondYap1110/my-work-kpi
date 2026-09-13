<?php

namespace App\Http\Requests\Project;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\Concerns\ChecksTagFitsAssignee;
use App\Http\Requests\Concerns\OnlyAdminAssignsTags;
use App\Models\Project;
use App\Models\ProjectTaskFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProjectTaskRequest extends FormRequest
{
    use ChecksTagFitsAssignee, OnlyAdminAssignsTags;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:project,id'],
            'assignee_id' => ['nullable', 'exists:staff,id'],
            'title' => ['required', 'string', 'max:255'],
            // Room to say what the work actually involves. The title is a
            // label; this is the brief, and what the appraiser reads later.
            'description' => ['nullable', 'string', 'max:5000'],
            'is_milestone' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(array_column(TaskStatus::cases(), 'value'))],
            'priority' => ['nullable', Rule::in(array_column(TaskPriority::cases(), 'value'))],
            'tag_id' => ['nullable', 'exists:project_tag,id'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            // As many files as the work needs, rather than the two fixed
            // "remark" and "invoice" slots this replaced. Each is recorded
            // against the task with who uploaded it - see ProjectTaskFile.
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'mimes:'.implode(',', ProjectTaskFile::ALLOWED_EXTENSIONS), 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->checkDatesAgainstProject($validator);
            $this->checkTagFitsAssignee($validator);
        });
    }

    /**
     * A task cannot run outside the project that contains it.
     */
    private function checkDatesAgainstProject(Validator $validator): void
    {
        $project = Project::find($this->input('project_id'));

        if (! $project) {
            return;
        }

        if ($this->filled('start_date') && $this->date('start_date')->lt($project->start_date)) {
            $validator->errors()->add('start_date', 'Task cannot start before the project starts.');
        }

        if ($this->filled('due_date') && $this->date('due_date')->gt($project->end_date)) {
            $validator->errors()->add('due_date', 'Task cannot be due after the project ends.');
        }
    }
}
