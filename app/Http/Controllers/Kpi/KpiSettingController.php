<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\StaffPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * A position's Project KPI: how its KPI score's 100 points split between
 * projects and KPI objectives, and the project marks that earn the full project
 * points. Set from the position's KPI page.
 */
class KpiSettingController extends Controller
{
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
