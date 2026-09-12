<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKpiRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\StaffPosition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * A "KPI" is a position with objectives attached - there is no KPI record of
 * its own. Assigning one flips the position's has_kpi; unassigning clears
 * it and removes the objectives.
 */
class KpiController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'KPI', 'route' => '', 'active' => false],
            ['name' => 'Manage KPI', 'route' => '', 'active' => true],
        ];
    }


    public function store(StoreKpiRequest $request): RedirectResponse|JsonResponse
    {
        $position = StaffPosition::findOrFail($request->integer('position_id'));

        // Guarded here as well as in the UI: the Administrator position is
        // the access gate, not a reviewed job.
        if ($position->isAdministrator()) {
            return back()->withErrors(['position_id' => 'The Administrator position is not assessed.']);
        }

        $position->update(['has_kpi' => true]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        // A KPI with no objectives is useless, so assigning one drops the
        // user straight into adding them.
        return redirect()
            ->route('kpi.objectives.index', $position->id)
            ->with('status', 'KPI assigned. Add its objectives below.');
    }

    public function destroy(StaffPosition $position): RedirectResponse
    {
        $position->objectives()->delete();
        $position->update(['has_kpi' => false]);

        return back()->with('status', 'KPI removed from this position.');
    }
}
