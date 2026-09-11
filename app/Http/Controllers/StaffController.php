<?php

namespace App\Http\Controllers;

use App\Enums\ObjectiveType;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Mail\StaffWelcomeMail;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\Team;
use App\Services\StaffKpiScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        $current = match (request()->route()->getName()) {
            'staff.create' => 'Add Member',
            'staff.edit' => 'Edit Member',
            'staff.view-kpi' => 'View Member KPI',
            default => null,
        };

        if ($current === null) {
            return [
                ['name' => 'Member', 'route' => '', 'active' => true],
            ];
        }

        return [
            ['name' => 'Member', 'route' => 'staff.index', 'active' => false],
            ['name' => $current, 'route' => '', 'active' => true],
        ];
    }

    public function index(): View
    {
        return view('staff.index');
    }

    public function create(): View
    {
        return view('staff.create', [
            'positions' => StaffPosition::orderBy('position_name')->get(),
            'teams' => Team::orderBy('team_name')->get(),
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('photo');
        $data['staffimg'] = $this->storePhoto($request) ?? 'default.jpg';
        $data['staffstatus'] = true;
        // Real password is set by the staff member via the emailed link
        // below; this placeholder is never shown or usable as-is.
        $data['password'] = Hash::make(Str::random(40));

        $staff = Staff::create($data);

        $token = Password::broker()->createToken($staff);
        Mail::to($staff->email)->send(new StaffWelcomeMail($staff, $token));

        return redirect()->route('staff.index')->with('status', 'Member added successfully. A welcome email has been sent to set their password.');
    }

    public function edit(Staff $staff): View
    {
        return view('staff.edit', [
            'staff' => $staff,
            'positions' => StaffPosition::orderBy('position_name')->get(),
            'teams' => Team::orderBy('team_name')->get(),
        ]);
    }

    public function update(UpdateStaffRequest $request, Staff $staff): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'staffstatus']);
        $data['staffstatus'] = $request->boolean('staffstatus');

        if ($photo = $this->storePhoto($request)) {
            $data['staffimg'] = $photo;
        }

        $staff->update($data);

        return redirect()->route('staff.index')->with('status', 'Member updated successfully.');
    }

    public function toggleStatus(Staff $staff): RedirectResponse
    {
        $staff->update(['staffstatus' => ! $staff->staffstatus]);

        return back()->with('status', 'Member status updated successfully.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $staff->delete();

        return back()->with('status', 'Member deleted successfully.');
    }

    /**
     * Fields the live duplicate check may be asked about. An allowlist, so a
     * crafted request can't probe arbitrary columns.
     *
     * @var array<int, string>
     */
    private const UNIQUE_FIELDS = ['email', 'ic', 'contact'];

    /**
     * Live duplicate check for the unique fields on the staff form.
     *
     * Advisory only - StoreStaffRequest/UpdateStaffRequest still validate on
     * submit, and the database has unique constraints behind that. This just
     * tells the user before they fill in the rest of the form.
     *
     * Soft-deleted staff release their values, matching the validation rules,
     * and `ignore` lets the edit form skip the record being edited so it
     * doesn't flag the staff member's own details.
     */
    public function checkUnique(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'field' => ['required', 'string', Rule::in(self::UNIQUE_FIELDS)],
            'value' => ['required', 'string', 'max:255'],
            'ignore' => ['nullable', 'integer'],
        ]);

        $taken = Staff::withoutTrashed()
            ->where($validated['field'], $validated['value'])
            ->when($validated['ignore'] ?? null, fn ($query, $id) => $query->where('staff_id', '!=', $id))
            ->exists();

        return response()->json(['taken' => $taken]);
    }

    public function viewKpi(Request $request, Staff $staff, StaffKpiScoreService $scoreService): View
    {
        $staff->load(['position.kpi.objectives.info', 'position.kpi.objectives.mark', 'team']);

        // project_kpi only references the objective *catalog* row
        // (kojbInfo_id), so recovering each entry's Standard/Extra type
        // means looking it up via the staff's own KPI objectives.
        $objectiveTypeByInfoId = $staff->position?->kpi
            ?->objectives
            ->keyBy('kojbInfo_id')
            ->map(fn ($objective) => $objective->obj_type)
            ?? collect();

        $completedProjects = $scoreService->completedTeamProjects($staff);
        $selectedProjectId = $request->integer('pid') ?: null;

        $entries = $staff->projectKpis()
            ->with(['project', 'objectiveInfo'])
            ->whereIn('project_id', $completedProjects->pluck('project_id'))
            ->when($selectedProjectId, fn ($q) => $q->where('project_id', $selectedProjectId))
            ->get();

        return view('staff.view-kpi', [
            'staff' => $staff,
            'completedProjects' => $completedProjects,
            'selectedProjectId' => $selectedProjectId,
            'standardEntries' => $entries->filter(fn ($entry) => $objectiveTypeByInfoId->get($entry->kojbInfo_id) === ObjectiveType::Standard),
            'extraEntries' => $entries->filter(fn ($entry) => $objectiveTypeByInfoId->get($entry->kojbInfo_id) === ObjectiveType::Extra),
            'overallScore' => $scoreService->totalScore($staff),
            'projectScore' => $selectedProjectId ? $scoreService->totalScore($staff, $selectedProjectId) : null,
        ]);
    }

    private function storePhoto(Request $request): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        $file = $request->file('photo');
        $filename = now()->format('dmYHis').'.'.$file->extension();

        Storage::disk('public')->putFileAs('staff-photos', $file, $filename);

        return $filename;
    }
}
