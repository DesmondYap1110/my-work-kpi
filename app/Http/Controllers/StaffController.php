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
    /**
     * Shared placeholder shown for staff without an uploaded photo.
     */
    public const DEFAULT_PHOTO = 'default.jpg';

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
        $data['photo'] = $this->storePhoto($request) ?? self::DEFAULT_PHOTO;
        $data['is_active'] = true;
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
        $data = $request->safe()->except(['photo', 'is_active']);
        $data['is_active'] = $request->boolean('is_active');

        if ($photo = $this->storePhoto($request)) {
            $this->deletePhoto($staff->photo);
            $data['photo'] = $photo;
        } elseif ($request->boolean('remove_photo')) {
            $this->deletePhoto($staff->photo);
            $data['photo'] = self::DEFAULT_PHOTO;
        }

        $staff->update($data);

        return redirect()->route('staff.index')->with('status', 'Member updated successfully.');
    }

    public function toggleStatus(Staff $staff): RedirectResponse
    {
        $staff->update(['is_active' => ! $staff->is_active]);

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
            ->when($validated['ignore'] ?? null, fn ($query, $id) => $query->where('id', '!=', $id))
            ->exists();

        return response()->json(['taken' => $taken]);
    }

    public function viewKpi(Request $request, Staff $staff, StaffKpiScoreService $scoreService): View
    {
        $staff->load(['position.objectives.infos', 'team']);

        $completedProjects = $scoreService->completedTeamProjects($staff);
        $selectedProjectId = $request->integer('pid') ?: null;

        $entries = $staff->projectKpis()
            ->with(['project', 'objectiveInfo'])
            ->whereIn('project_id', $completedProjects->pluck('id'))
            ->when($selectedProjectId, fn ($q) => $q->where('project_id', $selectedProjectId))
            ->get();

        return view('staff.view-kpi', [
            'staff' => $staff,
            'completedProjects' => $completedProjects,
            'selectedProjectId' => $selectedProjectId,
            // The Standard/Extra flag lives on the scored item itself now, so
            // each entry carries its own type through objectiveInfo.
            'standardEntries' => $entries->filter(fn ($entry) => $entry->objectiveInfo?->objective_type === ObjectiveType::Standard),
            'extraEntries' => $entries->filter(fn ($entry) => $entry->objectiveInfo?->objective_type === ObjectiveType::Extra),
            'overallScore' => $scoreService->totalScore($staff),
            'projectScore' => $selectedProjectId ? $scoreService->totalScore($staff, $selectedProjectId) : null,
        ]);
    }

    /**
     * Removes a staff member's uploaded photo from disk. The shared
     * placeholder is never deleted - every member without a photo points at
     * it, so removing it would break all of them.
     */
    private function deletePhoto(?string $filename): void
    {
        if (blank($filename) || $filename === self::DEFAULT_PHOTO) {
            return;
        }

        Storage::disk('public')->delete('staff-photos/'.$filename);
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
