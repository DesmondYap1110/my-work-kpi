<?php

namespace App\Http\Controllers;

use App\Enums\PhaseApprovalStatus;
use App\Enums\PhaseProgressStatus;
use App\Enums\ProjectStatus;
use App\Http\Requests\StoreProjectPhaseRequest;
use App\Http\Requests\UpdateProjectPhaseRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\ProjectPhaseFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProjectPhaseController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        $current = match (request()->route()->getName()) {
            'project-phases.create' => 'Add Project Phase',
            'project-phases.edit' => 'Edit Project Phase',
            default => null,
        };

        if ($current === null) {
            return [
                ['name' => 'Project', 'route' => '', 'active' => false],
                ['name' => 'Manage Project Phase', 'route' => '', 'active' => true],
            ];
        }

        return [
            ['name' => 'Project', 'route' => '', 'active' => false],
            ['name' => 'Manage Project Phase', 'route' => 'project-phases.index', 'active' => false],
            ['name' => $current, 'route' => '', 'active' => true],
        ];
    }

    public function index(): View
    {
        return view('project-phases.index');
    }

    /**
     * Attachment history for one phase, loaded on demand by the
     * "Attachments" button in the AJAX-driven list (project-phases.index).
     */
    public function attachments(ProjectPhase $projectPhase): \Illuminate\Http\JsonResponse
    {
        $files = $projectPhase->files()->latest('uploaded_at')->get()->map(fn ($file) => [
            'name' => $file->filename,
            'url' => Storage::disk('public')->url('project-phase-files/'.$file->filename),
            'uploaded_at' => $file->uploaded_at->format('d M Y H:i'),
        ]);

        return response()->json(['files' => $files]);
    }

    public function create(Request $request): View
    {
        $project = Project::findOrFail($request->integer('project_id'));

        return view('project-phases.create', compact('project'));
    }

    public function store(StoreProjectPhaseRequest $request): RedirectResponse
    {
        $project = Project::findOrFail($request->integer('project_id'));

        $data = $request->safe()->except(['remark_file', 'invoice_file']);
        $data['approval_status'] = PhaseApprovalStatus::NoSubmission;
        // No invoice yet => On-Hold; an invoice at creation moves it straight to Progress.
        $data['progress_status'] = $request->hasFile('invoice_file') ? PhaseProgressStatus::Progress : PhaseProgressStatus::OnHold;

        if ($remark = $this->storeFile($request, 'remark_file')) {
            $data['remark_file'] = $remark;
        }
        if ($invoice = $this->storeFile($request, 'invoice_file')) {
            $data['invoice_file'] = $invoice;
        }

        $phase = ProjectPhase::create($data);

        $this->logAttachments($phase, $request);

        $project->update(['status' => ProjectStatus::InProgress]);

        return redirect()->route('project-phases.index', ['project_id' => $project->id])
            ->with('status', 'Project phase added successfully.');
    }

    public function edit(ProjectPhase $projectPhase): View
    {
        return view('project-phases.edit', ['phase' => $projectPhase, 'project' => $projectPhase->project]);
    }

    public function update(UpdateProjectPhaseRequest $request, ProjectPhase $projectPhase): RedirectResponse
    {
        $data = $request->safe()->except(['remark_file', 'invoice_file']);

        if ($invoice = $this->storeFile($request, 'invoice_file')) {
            $data['invoice_file'] = $invoice;
            $data['progress_status'] = PhaseProgressStatus::Progress;
            // Fixes the legacy bug where adding an invoice on edit silently
            // failed to push the parent project back to In-Progress.
            $projectPhase->project->update(['status' => ProjectStatus::InProgress]);
        }

        if ($remark = $this->storeFile($request, 'remark_file')) {
            $data['remark_file'] = $remark;
        }

        $projectPhase->update($data);

        $this->logAttachments($projectPhase, $request);

        return redirect()->route('project-phases.index', ['project_id' => $projectPhase->project_id])
            ->with('status', 'Project phase updated successfully.');
    }

    public function approve(ProjectPhase $projectPhase): RedirectResponse
    {
        if ($projectPhase->approval_status === PhaseApprovalStatus::Approved) {
            return back()->withErrors(['phase' => 'This phase has already been approved.']);
        }

        $projectPhase->update([
            'approval_status' => PhaseApprovalStatus::Approved,
            'progress_status' => PhaseProgressStatus::Complete,
        ]);

        return back()->with('status', 'Project phase approved.');
    }

    public function reject(ProjectPhase $projectPhase): RedirectResponse
    {
        if ($projectPhase->approval_status === PhaseApprovalStatus::Rejected) {
            return back()->withErrors(['phase' => 'This phase has already been rejected.']);
        }

        $projectPhase->update([
            'approval_status' => PhaseApprovalStatus::Rejected,
            'progress_status' => PhaseProgressStatus::Complete,
        ]);

        return back()->with('status', 'Project phase rejected.');
    }

    public function destroy(ProjectPhase $projectPhase): RedirectResponse
    {
        $project = $projectPhase->project;
        $projectPhase->delete();

        if (! $project->phases()->exists()) {
            $project->update(['status' => ProjectStatus::Active]);
        }

        return back()->with('status', 'Project phase deleted successfully.');
    }

    private function storeFile(Request $request, string $field): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        $filename = now()->format('dmYHis').'-'.$field.'.'.$file->extension();

        Storage::disk('public')->putFileAs('project-phase-files', $file, $filename);

        return $filename;
    }

    private function logAttachments(ProjectPhase $phase, Request $request): void
    {
        foreach (['remark_file', 'invoice_file'] as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            ProjectPhaseFile::create([
                'phase_id' => $phase->id,
                'filename' => $phase->{$field},
                'uploaded_at' => now(),
                'staff_id' => Auth::id(),
            ]);
        }
    }
}
