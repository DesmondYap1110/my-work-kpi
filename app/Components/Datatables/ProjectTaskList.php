<?php

namespace App\Components\Datatables;

use App\Components\Filters\DateFilter;
use App\Components\Filters\SelectFilter;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Staff;
use App\Queries\ProjectTaskListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Every task across every project - "what is outstanding, and whose is it?".
 *
 * The per-project view lives on the project page itself; this is the flat list
 * you filter when you want work by person, status or due date rather than by
 * project.
 */
class ProjectTaskList extends Datatables
{
    public static function getTableColumns(): array
    {
        $columns = [
            'title' => 'Task',
            'project_title' => 'Project',
            'assignee' => 'Assignee',
            'tag' => 'Tag',
            'due_date' => 'Due',
            'status' => 'Status',
            'action' => 'Actions',
        ];

        // For anyone but the administrator every row is their own, so a column
        // repeating their name down the page says nothing.
        if (! static::viewerIsAdmin()) {
            unset($columns['assignee']);
        }

        return $columns;
    }

    /**
     * Staff reach this list as "My Tasks" - scoped to their own work by
     * ProjectTaskListQuery, which is what makes it safe to open.
     */
    public static function adminOnly(): bool
    {
        return false;
    }

    public function filters(): array
    {
        $statuses = collect(TaskStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();

        $filters = [
            new SelectFilter('project_id', 'Project', Project::orderBy('title')->pluck('title', 'id')->all()),
        ];

        // Every row already belongs to the viewer when they are not an
        // administrator, so a person filter would only ever have one answer.
        if ($this->viewerIsAdmin()) {
            $filters[] = new SelectFilter('assignee_id', 'Assignee', Staff::excludingAdmin()->orderBy('staff_name')->pluck('staff_name', 'id')->all());
        }

        return array_merge($filters, [
            new SelectFilter('status', 'Status', $statuses),
            new DateFilter('due_from', 'Due From'),
            new DateFilter('due_to', 'Due To'),
        ]);
    }

    public function centeredColumns(): array
    {
        return ['tag', 'due_date', 'status'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        // These three are rendered from relations, so there is no column on
        // project_task to sort them by.
        return $this->paginateFromRequest(
            app(ProjectTaskListQuery::class)->forRequest($request),
            $request,
            ['project_title' => null, 'assignee' => null, 'tag' => null]
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function (ProjectTask $task) {
            return array_filter([
                'title' => $this->titleCell($task),
                'project_title' => e($task->project->title ?? '-'),
                'assignee' => static::viewerIsAdmin() ? e($task->assignee->staff_name ?? 'Unassigned') : null,
                'tag' => $this->tagCell($task),
                'due_date' => $this->dueCell($task),
                'status' => $this->statusCell($task),
                'action' => $this->actionButtons($task),
            ], fn ($cell) => $cell !== null);
        })->all();

        return $this->respond($request, $result, $rows);
    }

    /**
     * A milestone earns a marker, and a subtask says whose it is - otherwise
     * the flat list loses the shape the project page shows.
     */
    private function titleCell(ProjectTask $task): string
    {
        $title = e($task->title);

        if ($task->is_milestone) {
            $title = '<i class="ri-flag-2-fill kpi-milestone-icon" title="Milestone"></i>'.$title;
        }

        if ($task->parent) {
            $title .= '<span class="kpi-item-desc">in '.e($task->parent->title).'</span>';
        }

        return $title;
    }

    /**
     * The tag carries the points, so showing it without them hides the thing
     * that actually matters about it.
     */
    private function tagCell(ProjectTask $task): string
    {
        if (! $task->tag) {
            return '-';
        }

        return '<span class="tb-status" id="tb-status-4">'.e($task->tag->name)
            .' &middot; '.rtrim(rtrim(number_format((float) $task->tag->points, 2), '0'), '.').'</span>';
    }

    private function dueCell(ProjectTask $task): string
    {
        if (! $task->due_date) {
            return '-';
        }

        $date = $task->due_date->format('d M Y');

        return $task->isOverdue()
            ? '<span class="tb-red-p">'.$date.'</span>'
            : $date;
    }

    /**
     * Status is a control, not a label: this is the list a person works from,
     * and moving a task along is the whole reason they opened it.
     *
     * See public/js/modules/status-select.js - the same dropdown the project
     * page uses, PATCHing the row rather than reloading the table.
     */
    private function statusCell(ProjectTask $task): string
    {
        $options = collect(TaskStatus::cases())
            ->map(fn (TaskStatus $status) => '<option value="'.$status->value.'"'
                .($task->status === $status ? ' selected' : '').'>'.e($status->label()).'</option>')
            ->implode('');

        return '<select class="form-control task-status js-task-status" aria-label="Task status"'
            .' data-url="'.route('project-tasks.status', $task->id).'">'.$options.'</select>';
    }

    private function actionButtons(ProjectTask $task): string
    {
        // Opening the project means the workspace where tasks are added and
        // deleted, so both belong to the administrator. What is left for
        // everyone else is the attachment list - the brief for the work.
        $buttons = [$this->tbButton('ri-attachment-2', 'tb-ac-btn-6', 'Attachments', ['id' => $task->id], 'js-show-attachments')];

        if (static::viewerIsAdmin()) {
            array_unshift($buttons, $this->tbLink(
                route('projects.show', $task->project_id),
                'ri-external-link-line',
                'tb-ac-btn-4',
                'Open in its project'
            ));

            $buttons[] = $this->tbDeleteForm(route('project-tasks.destroy', $task->id));
        }

        return implode(' ', $buttons);
    }
}
