<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\KpiSetting;
use App\Models\StaffPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * How much of a KPI score comes from delivering project work.
 *
 * One number for the company, with a per-position override for roles the
 * company answer does not fit - an office admin in a project-driven firm.
 */
class KpiSettingController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Project Setup', 'route' => '', 'active' => false],
            ['name' => 'Weighting', 'route' => '', 'active' => true],
        ];
    }

    /**
     * The company figure only. A position's own figure is set on that
     * position's KPI page, next to the objectives it weighs against - see
     * updatePosition().
     */
    public function edit(): View
    {
        return view('kpi-settings.edit', [
            'setting' => KpiSetting::current(),
            'overrides' => StaffPosition::excludingAdmin()->where(fn ($q) => $q->whereNotNull('project_weight')->orWhereNotNull('project_target'))->orderBy('position_name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_weight' => ['required', 'integer', 'between:0,100'],
        ], [
            'project_weight.between' => 'The company weighting must be between 0 and 100.',
        ]);

        KpiSetting::current()->update(['project_weight' => $validated['project_weight']]);

        return back()->with('status', 'Weighting updated successfully.');
    }

    /**
     * One position's own weighting, saved from its KPI page.
     *
     * Blank means "follow the company figure", which is why it is stored as
     * NULL rather than copied from the company number - a copy would stop
     * following when the company figure changes.
     */
    public function updatePosition(Request $request, StaffPosition $position): RedirectResponse
    {
        abort_if($position->isAdministrator(), 404);

        $validated = $request->validate([
            'project_weight' => ['nullable', 'integer', 'between:0,100'],
            // The project marks this position is expected to earn in a review
            // period. Blank measures against the tasks they were given instead.
            'project_target' => ['nullable', 'numeric', 'min:0.01', 'max:100000'],
        ], [
            'project_weight.between' => 'Points from projects must be between 0 and 100.',
            'project_target.min' => 'The project target must be more than 0, or left blank.',
        ]);

        $weight = $validated['project_weight'] ?? null;
        $target = $validated['project_target'] ?? null;

        $position->update([
            'project_weight' => $weight === null ? null : (int) $weight,
            'project_target' => $target === null ? null : $target,
        ]);

        return back()->with('status', 'Project KPI saved for '.$position->position_name.'.');
    }
}
