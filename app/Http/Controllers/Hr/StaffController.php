<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreStaffRequest;
use App\Http\Requests\Hr\UpdateStaffRequest;
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
        // A staff member reading their own scorecard did not come from the
        // Member list and cannot open it, so the trail is one crumb long.
        if (request()->routeIs('my.kpi')) {
            return [['name' => 'My KPI', 'route' => '', 'active' => true]];
        }

        $current = match (request()->route()->getName()) {
            'staff.create' => 'Add Member',
            'staff.edit' => 'Edit Member',
            'staff.view-kpi' => 'View Member KPI',
            default => null,
        };

        if ($current === null) {
            return [
                ['name' => 'Human Resource', 'route' => '', 'active' => false],
                ['name' => 'Member', 'route' => '', 'active' => true],
            ];
        }

        return [
            ['name' => 'Human Resource', 'route' => '', 'active' => false],
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
            // Only positions a member can actually be assessed in - see
            // StaffPosition::acceptsMembers().
            'positions' => StaffPosition::acceptingMembers()->orderBy('position_name')->get(),
            'teams' => Team::orderBy('team_name')->get(),
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'password']);
        $data['photo'] = $this->storePhoto($request) ?? self::DEFAULT_PHOTO;
        $data['is_active'] = true;

        // A password typed in by the administrator is handed over directly, so
        // there is nothing to email. Left blank - the normal case - the member
        // sets their own through the welcome link, and this placeholder is
        // never shown or usable as-is.
        $chosen = $request->filled('password') ? $request->input('password') : null;
        $data['password'] = Hash::make($chosen ?? Str::random(40));

        $staff = Staff::create($data);

        if ($chosen !== null) {
            return redirect()->route('staff.index')
                ->with('status', 'Member added successfully with the password you set. No welcome email was sent.');
        }

        $token = Password::broker()->createToken($staff);
        Mail::to($staff->email)->send(new StaffWelcomeMail($staff, $token));

        return redirect()->route('staff.index')->with('status', 'Member added successfully. A welcome email has been sent to set their password.');
    }

    public function edit(Staff $staff): View
    {
        return view('staff.edit', [
            'staff' => $staff,
            // Plus the member's own position even if its KPI has since been
            // removed: otherwise the dropdown silently shows nothing selected,
            // and saving an unrelated change would appear to demand a move.
            'positions' => StaffPosition::query()
                // orWhere on the key column: Laravel 10's builder has
                // whereKey() but no orWhereKey().
                ->where(fn ($q) => $q->acceptingMembers()->orWhere('staff_position.id', $staff->position_id))
                ->orderBy('position_name')
                ->get(),
            'teams' => Team::orderBy('team_name')->get(),
        ]);
    }

    public function update(UpdateStaffRequest $request, Staff $staff): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'is_active', 'password']);
        $data['is_active'] = $request->boolean('is_active');

        // Blank means "leave it alone", so the key is only added when one was
        // actually typed. Without this, opening the form and saving anything
        // else would set every member's password to the empty string.
        $passwordChanged = $request->filled('password');

        if ($passwordChanged) {
            $data['password'] = Hash::make($request->input('password'));
        }

        if ($photo = $this->storePhoto($request)) {
            $this->deletePhoto($staff->photo);
            $data['photo'] = $photo;
        } elseif ($request->boolean('remove_photo')) {
            $this->deletePhoto($staff->photo);
            $data['photo'] = self::DEFAULT_PHOTO;
        }

        $staff->update($data);

        return redirect()->route('staff.index')->with('status', $passwordChanged
            ? 'Member updated, and their password has been changed. They will need the new one to sign in.'
            : 'Member updated successfully.');
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
        return view('staff.view-kpi', $this->kpiPayload($request, $staff, $scoreService));
    }

    /**
     * The same scorecard, read by the person it is about.
     *
     * Staff have no Member list to come from and nothing to edit here, so the
     * page is handed the same data with $self set and drops the two buttons
     * that only make sense to an administrator.
     */
    public function myKpi(Request $request, StaffKpiScoreService $scoreService): View
    {
        return view('staff.view-kpi', array_merge(
            $this->kpiPayload($request, $request->user(), $scoreService),
            ['self' => true]
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function kpiPayload(Request $request, Staff $staff, StaffKpiScoreService $scoreService): array
    {
        $staff->load(['position.objectives.infos', 'team']);

        $completedProjects = $scoreService->completedProjectsFor($staff);
        $selectedProjectId = $request->integer('pid') ?: null;

        // Only a project this member actually completed may be picked; any
        // other id in the query string just shows all of them.
        if ($selectedProjectId && ! $completedProjects->contains('id', $selectedProjectId)) {
            $selectedProjectId = null;
        }

        return [
            'staff' => $staff,
            'completedProjects' => $completedProjects,
            'selectedProjectId' => $selectedProjectId,
            // The score out of 100 and both halves of it - project marks and
            // KPI objectives - worked out by the same rule.
            'finalScore' => $scoreService->finalScore($staff),
            // The objectives half item by item, optionally for one project.
            'objectiveBreakdown' => $scoreService->objectiveBreakdown($staff, $selectedProjectId),
            'projectScore' => $selectedProjectId ? $scoreService->totalScore($staff, $selectedProjectId) : null,
            'self' => false,
        ];
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
