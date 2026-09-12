<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProjectTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:project,id'],
            // A subtask's parent must belong to the same project, checked in
            // withValidator(); a parent of its own is not allowed, so the tree
            // stays two deep like ClickUp's.
            'parent_id' => ['nullable', 'exists:project_task,id'],
            'assignee_id' => ['nullable', 'exists:staff,id'],
            'title' => ['required', 'string', 'max:255'],
            'is_milestone' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(array_column(TaskStatus::cases(), 'value'))],
            'priority' => ['nullable', Rule::in(array_column(TaskPriority::cases(), 'value'))],
            'tag_id' => ['nullable', 'exists:project_tag,id'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'remark_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'invoice_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->checkDatesAgainstProject($validator);
            $this->checkParentBelongsToProject($validator);
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

    private function checkParentBelongsToProject(Validator $validator): void
    {
        if (! $this->filled('parent_id')) {
            return;
        }

        // Editing is the only way a task could be pointed at itself, which
        // would orphan it from the tree and hide it from every listing.
        $task = $this->route('project_task');

        if ($task && (int) $this->input('parent_id') === (int) $task->id) {
            $validator->errors()->add('parent_id', 'A task cannot be its own parent.');

            return;
        }

        $parent = ProjectTask::find($this->input('parent_id'));

        if (! $parent) {
            return;
        }

        if ((int) $parent->project_id !== (int) $this->input('project_id')) {
            $validator->errors()->add('parent_id', 'That parent task belongs to a different project.');
        }

        if ($parent->parent_id !== null) {
            $validator->errors()->add('parent_id', 'A subtask cannot have subtasks of its own.');
        }
    }
}
