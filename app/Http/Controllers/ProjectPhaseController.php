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
        $files = $projectPhase->files()->latest('PPdatetime')->get()->map(fn ($file) => [
            'name' => $file->PPfilename,
            'url' => Storage::disk('public')->url('project-phase-files/'.$file->PPfilename),
            'uploaded_at' => $file->PPdatetime->format('d M Y H:i'),
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
        $project = Project::findOrFail($request->integer('p_ID'));

        $data = $request->safe()->except(['p_Remark', 'p_Invoice']);
        $data['p_Status'] = PhaseApprovalStatus::NoSubmission;
        // No invoice yet => On-Hold; an invoice at creation moves it straight to Progress.
        $data['p_ppstatus'] = $request->hasFile('p_Invoice') ? PhaseProgressStatus::Progress : PhaseProgressStatus::OnHold;

        if ($remark = $this->storeFile($request, 'p_Remark')) {
            $data['p_Remark'] = $remark;
        }
        if ($invoice = $this->storeFile($request, 'p_Invoice')) {
            $data['p_Invoice'] = $invoice;
        }

        $phase = ProjectPhase::create($data);

        $this->logAttachments($phase, $request);

        $project->update(['p_status' => ProjectStatus::InProgress]);

        return redirect()->route('project-phases.index', ['project_id' => $project->project_id])
            ->with('status', 'Project phase added successfully.');
    }

    public function edit(ProjectPhase $projectPhase): View
    {
        return view('project-phases.edit', ['phase' => $projectPhase, 'project' => $projectPhase->project]);
    }

    public function update(UpdateProjectPhaseRequest $request, ProjectPhase $projectPhase): RedirectResponse
    {
        $data = $request->safe()->except(['p_Remark', 'p_Invoice']);

        if ($invoice = $this->storeFile($request, 'p_Invoice')) {
            $data['p_Invoice'] = $invoice;
            $data['p_ppstatus'] = PhaseProgressStatus::Progress;
            // Fixes the legacy bug where adding an invoice on edit silently
            // failed to push the parent project back to In-Progress.
            $projectPhase->project->update(['p_status' => ProjectStatus::InProgress]);
        }

        if ($remark = $this->storeFile($request, 'p_Remark')) {
            $data['p_Remark'] = $remark;
        }

        $projectPhase->update($data);

        $this->logAttachments($projectPhase, $request);

        return redirect()->route('project-phases.index', ['project_id' => $projectPhase->p_ID])
            ->with('status', 'Project phase updated successfully.');
    }

    public function approve(ProjectPhase $projectPhase): RedirectResponse
    {
        if ($projectPhase->p_Status === PhaseApprovalStatus::Approved) {
            return back()->withErrors(['phase' => 'This phase has already been approved.']);
        }

        $projectPhase->update([
            'p_Status' => PhaseApprovalStatus::Approved,
            'p_ppstatus' => PhaseProgressStatus::Complete,
        ]);

        return back()->with('status', 'Project phase approved.');
    }

    public function reject(ProjectPhase $projectPhase): RedirectResponse
    {
        if ($projectPhase->p_Status === PhaseApprovalStatus::Rejected) {
            return back()->withErrors(['phase' => 'This phase has already been rejected.']);
        }

        $projectPhase->update([
            'p_Status' => PhaseApprovalStatus::Rejected,
            'p_ppstatus' => PhaseProgressStatus::Complete,
        ]);

        return back()->with('status', 'Project phase rejected.');
    }

    public function destroy(ProjectPhase $projectPhase): RedirectResponse
    {
        $project = $projectPhase->project;
        $projectPhase->delete();

        if (! $project->phases()->exists()) {
            $project->update(['p_status' => ProjectStatus::Active]);
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
        foreach (['p_Remark', 'p_Invoice'] as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            ProjectPhaseFile::create([
                'p_pID' => $phase->p_PID,
                'PPfilename' => $phase->{$field},
                'PPdatetime' => now(),
                'staff_ID' => Auth::id(),
            ]);
        }
    }
}
