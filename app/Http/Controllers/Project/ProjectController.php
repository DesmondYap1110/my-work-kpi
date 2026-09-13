<?php

namespace App\Http\Controllers\Project;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\Project;
use App\Models\ProjectTag;
use App\Models\ProjectTask;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        $current = match (request()->route()->getName()) {
            'projects.show' => request()->route('project')?->title,
            default => null,
        };

        if ($current === null) {
            return [
                ['name' => 'Project Setup', 'route' => '', 'active' => false],
                ['name' => 'Project', 'route' => '', 'active' => true],
            ];
        }

        return [
            ['name' => 'Project Setup', 'route' => '', 'active' => false],
            ['name' => 'Project', 'route' => 'projects.index', 'active' => false],
            ['name' => $current, 'route' => '', 'active' => true],
        ];
    }

    public function index(): View
    {
        return view('projects.index');
    }

    /**
     * The project workspace: its tasks, grouped by status.
     *
     * Everything is added and edited in place here, so the structure you are
     * building stays on screen while you build it.
     */
    public function show(Project $project): View
    {
        $tasks = $project->tasks()
            ->with(['assignee', 'tag', 'files.staff'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('projects.show', [
            'project' => $project,
            // Keyed by status value so the view can render every column,
            // including the empty ones - a board with no "Blocked" column
            // reads as though nothing can be blocked.
            'tasksByStatus' => $tasks->groupBy(fn (ProjectTask $task) => $task->status->value),
            'taskCount' => $tasks->count(),
            // Anyone active can be given a task - work is assigned to a
            // person, not to whichever team the project belonged to.
            'assignees' => Staff::active()->excludingAdmin()->orderBy('staff_name')->get(),
            'tags' => ProjectTag::active()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        Project::create($request->validated() + [
            'assigned_date' => now()->toDateString(),
            'status' => ProjectStatus::Active,
        ]);

        return redirect()->route('projects.index')->with('status', 'Project added successfully.');
    }

    /**
     * Saves the Edit Project dialog on the project's own page - there is no
     * separate edit screen - and returns to that page.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        // The page only offers editing while the project is open; this is the
        // rule behind it, for a request made without the page.
        if (! $project->isEditable()) {
            return back()->withErrors(['project' => 'A '.strtolower($project->status->label()).' project can no longer be edited.']);
        }

        $data = $request->safe()->except(['mark_complete', '_form']);

        // The "mark complete" checkbox is only offered while the project
        // isn't already Completed, and only ever moves it forward to
        // Completed — never used to write any other status.
        if ($request->boolean('mark_complete') && $project->status !== ProjectStatus::Completed) {
            $data['status'] = ProjectStatus::Completed;
            $data['complete_date'] = now();
        }

        $project->update($data);

        if ($project->status === ProjectStatus::Completed) {
            $project->seedKpiEntriesForCompletion();
        }

        return redirect()->route('projects.show', $project)->with('status', $project->status === ProjectStatus::Completed
            ? 'Project updated and marked as completed.'
            : 'Project updated successfully.');
    }

    /**
     * Cancels a project, which requires saying why.
     *
     * The reason is kept with who cancelled it and when: a cancelled project
     * stops taking new work, and the question anyone asks of it later is why.
     */
    public function cancel(Request $request, Project $project): RedirectResponse
    {
        if (! in_array($project->status, [ProjectStatus::Active, ProjectStatus::InProgress], true)) {
            return back()->withErrors(['project' => 'Only active or in-progress projects can be cancelled.']);
        }

        $request->validate([
            'cancel_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'cancel_reason.required' => 'Please give a reason for cancelling this project.',
            'cancel_reason.min' => 'Please give a little more detail on why the project is being cancelled.',
        ]);

        $project->update([
            'status' => ProjectStatus::Cancelled,
            'cancel_reason' => trim($request->input('cancel_reason')),
            'cancelled_at' => now(),
            'cancelled_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Project "'.$project->title.'" cancelled.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        if ($project->status !== ProjectStatus::Active) {
            return back()->withErrors(['project' => 'Only active projects can be deleted.']);
        }

        $project->delete();

        return back()->with('status', 'Project deleted successfully.');
    }
}
