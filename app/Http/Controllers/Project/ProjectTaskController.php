<?php

namespace App\Http\Controllers\Project;

use App\Enums\PhaseApprovalStatus;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectTaskRequest;
use App\Http\Requests\Project\UpdateProjectTaskRequest;
use App\Http\Requests\Project\UpdateTaskStatusRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Tasks on a project: the work itself, its owner and whether it is done.
 *
 * Adding and editing happen inline on the project page rather than on separate
 * screens, so the list you are building stays visible while you build it. This
 * controller therefore has no create/edit views - only index, which is the
 * cross-project list.
 */
class ProjectTaskController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        if (request()->routeIs('my.tasks')) {
            return [['name' => 'My Tasks', 'route' => '', 'active' => true]];
        }

        return [
            ['name' => 'Project Setup', 'route' => '', 'active' => false],
            ['name' => 'Task', 'route' => '', 'active' => true],
        ];
    }

    /**
     * Every task, across projects - the successor to the old phase list.
     */
    public function index(): View
    {
        return view('project-tasks.index');
    }

    /**
     * The same list, reached by a staff member as their own work.
     *
     * There is no second query here: ProjectTaskListQuery already narrows the
     * rows to whoever is asking, so this is the administrator's page under a
     * different title. Only staff are given the link - an administrator has
     * no tasks of their own to find here.
     */
    public function mine(): View
    {
        return view('project-tasks.index', ['heading' => 'My Tasks']);
    }

    public function store(StoreProjectTaskRequest $request): RedirectResponse
    {
        $project = Project::findOrFail($request->integer('project_id'));

        // The page hides Add Task on a cancelled project; this is the rule
        // behind it, since project_id arrives from a form.
        if (! $project->acceptsNewTasks()) {
            return back()->withErrors(['project' => 'This project has been cancelled, so no new tasks can be added to it.']);
        }

        $data = $request->safe()->except('attachments');
        $data['is_milestone'] = $request->boolean('is_milestone');
        $data['approval_status'] = PhaseApprovalStatus::NoSubmission;
        $data['sort_order'] = ((int) $project->tasks()->max('sort_order')) + 1;

        $task = ProjectTask::create($data);

        $attached = $this->storeAttachments($task, $request);
        $this->syncProjectStatus($project);

        return back()->with('status', 'Task added successfully.'.$this->attachmentNote($attached));
    }

    public function update(UpdateProjectTaskRequest $request, ProjectTask $projectTask): RedirectResponse
    {
        $data = $request->safe()->except('attachments');
        $data['is_milestone'] = $request->boolean('is_milestone');

        $projectTask->update($data);

        $attached = $this->storeAttachments($projectTask, $request);
        $this->syncProjectStatus($projectTask->project);

        return back()->with('status', 'Task updated successfully.'.$this->attachmentNote($attached));
    }

    /**
     * The status dropdown on a row. Answers JSON so the row can repaint
     * without reloading the page and losing your place in a long list.
     *
     * completed_at is stamped by the model, not here - see ProjectTask::booted().
     */
    public function updateStatus(UpdateTaskStatusRequest $request, ProjectTask $projectTask): JsonResponse
    {
        $this->authorizeTask($projectTask);

        $projectTask->update(['status' => $request->integer('status')]);
        $this->syncProjectStatus($projectTask->project);

        $status = $projectTask->status;

        return response()->json([
            'status' => 'ok',
            'label' => $status->label(),
            'colour_id' => $status->colourId(),
            'progress' => $projectTask->progress,
            'completed_at' => $projectTask->completed_at?->format('d M Y'),
        ]);
    }

    public function destroy(ProjectTask $projectTask): RedirectResponse
    {
        $project = $projectTask->project;

        $projectTask->delete();

        $this->syncProjectStatus($project);

        return back()->with('status', 'Task deleted successfully.');
    }

    public function approve(ProjectTask $projectTask): RedirectResponse
    {
        if ($projectTask->approval_status === PhaseApprovalStatus::Approved) {
            return back()->withErrors(['task' => 'This task has already been approved.']);
        }

        $projectTask->update([
            'approval_status' => PhaseApprovalStatus::Approved,
            'status' => TaskStatus::Done,
        ]);

        return back()->with('status', 'Task approved.');
    }

    public function reject(ProjectTask $projectTask): RedirectResponse
    {
        if ($projectTask->approval_status === PhaseApprovalStatus::Rejected) {
            return back()->withErrors(['task' => 'This task has already been rejected.']);
        }

        $projectTask->update(['approval_status' => PhaseApprovalStatus::Rejected]);

        return back()->with('status', 'Task rejected.');
    }

    public function attachments(ProjectTask $projectTask): JsonResponse
    {
        $this->authorizeTask($projectTask);

        $files = $projectTask->files()->with('staff')->get()->map(fn ($file) => [
            'name' => $file->displayName(),
            'url' => $file->url(),
            'uploaded_at' => $file->uploaded_at->format('d M Y H:i')
                .($file->staff ? ' · '.$file->staff->staff_name : ''),
        ]);

        return response()->json(['files' => $files]);
    }

    /**
     * The two actions a staff member may take on a task - move it along, and
     * read what is attached to it - reach past the administrator-only group,
     * so each has to establish for itself that the task is theirs.
     *
     * Without this, any signed-in member could change the status of a task
     * belonging to anyone else simply by knowing its id, and delivery points
     * are earned by tasks reaching Done.
     */
    private function authorizeTask(ProjectTask $task): void
    {
        $staff = Auth::user();

        abort_unless($staff && ($staff->isAdmin() || (int) $task->assignee_id === (int) $staff->id), 403);
    }

    /**
     * Keeps the project's own status honest as its tasks move.
     *
     * A project with no tasks is back to Active; one with work under way is
     * In-Progress. Completed and Cancelled are decisions a person makes, so
     * they are never overwritten here.
     */
    private function syncProjectStatus(Project $project): void
    {
        if (in_array($project->status, [ProjectStatus::Completed, ProjectStatus::Cancelled], true)) {
            return;
        }

        $project->update([
            'status' => $project->tasks()->exists() ? ProjectStatus::InProgress : ProjectStatus::Active,
        ]);
    }

    /**
     * Saves whatever was uploaded with the form and records each file against
     * the task.
     *
     * Stored under a unique name rather than the one it arrived with: two
     * people attaching "screenshot.png" to different tasks must not overwrite
     * each other, and a filename from a browser is user input. The original
     * name is kept in the row so the attachment list still reads like the file
     * the person chose.
     *
     * @return int  how many were attached
     */
    private function storeAttachments(ProjectTask $task, Request $request): int
    {
        $files = array_filter($request->file('attachments') ?? []);

        foreach ($files as $file) {
            $stored = Str::uuid().'.'.$this->safeExtension($file);

            Storage::disk('public')->putFileAs('project-task-files', $file, $stored);

            ProjectTaskFile::create([
                'task_id' => $task->id,
                'filename' => $stored,
                'original_name' => $file->getClientOriginalName(),
                'uploaded_at' => now(),
                'staff_id' => Auth::id(),
            ]);
        }

        return count($files);
    }

    /**
     * The extension to store the file under.
     *
     * extension() alone guesses from the MIME type, which turns a .csv into a
     * .txt - the content survives but the browser then opens a spreadsheet as
     * plain text. The uploader's own extension is better, but only when it is
     * one we allow: it is user input, and these files are served from a public
     * directory. Anything unrecognised falls back to the guess.
     */
    private function safeExtension($file): string
    {
        foreach ([$file->getClientOriginalExtension(), $file->extension()] as $candidate) {
            $candidate = strtolower((string) $candidate);

            if (in_array($candidate, ProjectTaskFile::ALLOWED_EXTENSIONS, true)) {
                return $candidate;
            }
        }

        // Neither the uploader's extension nor the guessed one is on the list.
        // Validation should already have refused the file, so this is the
        // belt to that braces: store it as something inert rather than
        // trusting whichever name got this far.
        return 'dat';
    }

    private function attachmentNote(int $attached): string
    {
        return $attached > 0
            ? ' '.$attached.' '.Str::plural('file', $attached).' attached.'
            : '';
    }

    /**
     * Removes one attachment, from the task and from disk.
     *
     * The file is checked against the task in the URL rather than trusted:
     * the id comes from a form, and nothing here should reach another task's
     * attachments.
     */
    public function destroyAttachment(ProjectTask $projectTask, ProjectTaskFile $file): RedirectResponse
    {
        abort_unless($file->task_id === $projectTask->id, 404);

        Storage::disk('public')->delete('project-task-files/'.$file->filename);
        $file->forceDelete();

        return back()->with('status', 'Attachment removed.');
    }
}
