<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        $current = match (request()->route()->getName()) {
            'projects.create' => 'Add Project',
            'projects.edit' => 'Edit Project',
            default => null,
        };

        if ($current === null) {
            return [
                ['name' => 'Project', 'route' => '', 'active' => false],
                ['name' => 'Manage Project', 'route' => '', 'active' => true],
            ];
        }

        return [
            ['name' => 'Project', 'route' => '', 'active' => false],
            ['name' => 'Manage Project', 'route' => 'projects.index', 'active' => false],
            ['name' => $current, 'route' => '', 'active' => true],
        ];
    }

    public function index(): View
    {
        return view('projects.index');
    }

    public function create(): View
    {
        return view('projects.create', ['teams' => Team::active()->orderBy('team_name')->get()]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        Project::create($request->validated() + [
            'assigned_date' => now()->toDateString(),
            'status' => ProjectStatus::Active,
        ]);

        return redirect()->route('projects.index')->with('status', 'Project added successfully.');
    }

    public function edit(Project $project): View
    {
        return view('projects.edit', ['project' => $project, 'teams' => Team::active()->orderBy('team_name')->get()]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $request->safe()->except('mark_complete');

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

        return redirect()->route('projects.index')->with('status', 'Project updated successfully.');
    }

    public function cancel(Project $project): RedirectResponse
    {
        if (! in_array($project->status, [ProjectStatus::Active, ProjectStatus::InProgress], true)) {
            return back()->withErrors(['project' => 'Only active or in-progress projects can be cancelled.']);
        }

        $project->update(['status' => ProjectStatus::Cancelled]);

        return back()->with('status', 'Project cancelled.');
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
