<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKpiRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\StaffPosition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * A "KPI" is a position with objectives attached - there is no KPI record of
 * its own, and no flag either. A position "has a KPI" exactly when it has
 * objectives, so assigning one means going and adding them, and removing one
 * means deleting them.
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

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        // Nothing to record: a KPI *is* its objectives. This just opens the
        // page where they get added - which is what "assign" always meant.
        return redirect()
            ->route('kpi.objectives.index', $position->id)
            ->with('status', 'Add the objectives for this position below.');
    }

    public function destroy(StaffPosition $position): RedirectResponse
    {
        // Deleting the objectives is what removes the KPI - each takes its
        // own scored items with it (see KpiObjective::booted()).
        $position->objectives()->get()->each->delete();

        return back()->with('status', 'KPI removed from this position.');
    }
}
