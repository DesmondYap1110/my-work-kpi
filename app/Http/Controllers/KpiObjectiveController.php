<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKpiObjectiveRequest;
use App\Http\Requests\UpdateKpiObjectiveRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\Kpi;
use App\Models\KpiObjective;
use App\Models\KpiObjectiveInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KpiObjectiveController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'KPI', 'route' => '', 'active' => false],
            ['name' => 'Manage KPI', 'route' => 'kpi.index', 'active' => false],
            ['name' => 'Add KPI Objective', 'route' => '', 'active' => true],
        ];
    }

    public function index(Kpi $kpi): View
    {
        return view('kpi.objectives.index', [
            'kpi' => $kpi->load('position'),
            'objectiveCatalog' => KpiObjectiveInfo::orderBy('kojbInfo_title')->get(),
        ]);
    }

    public function store(StoreKpiObjectiveRequest $request, Kpi $kpi): RedirectResponse
    {
        // A transaction here is a deliberate fix over the legacy page,
        // which inserted kpi_objective then kpi_objective_mark as two
        // unguarded statements - a failure between them left an objective
        // with no mark row at all.
        DB::transaction(function () use ($request, $kpi) {
            $objective = $kpi->objectives()->create([
                'kojbInfo_id' => $request->integer('kojbInfo_id'),
                'obj_type' => $request->integer('obj_type'),
            ]);

            $objective->mark()->create([
                'objmk_2' => $request->boolean('objmk_2'),
                'objmk_1' => $request->boolean('objmk_1'),
                'objmk_0' => $request->boolean('objmk_0'),
                'objmk_n1' => $request->boolean('objmk_n1'),
                'objmk_n2' => $request->boolean('objmk_n2'),
            ]);
        });

        return back()->with('status', 'Objective added successfully.');
    }

    public function update(UpdateKpiObjectiveRequest $request, Kpi $kpi, KpiObjective $objective): RedirectResponse
    {
        DB::transaction(function () use ($request, $objective) {
            $objective->update(['obj_type' => $request->integer('obj_type')]);

            $objective->mark()->updateOrCreate([], [
                'objmk_2' => $request->boolean('objmk_2'),
                'objmk_1' => $request->boolean('objmk_1'),
                'objmk_0' => $request->boolean('objmk_0'),
                'objmk_n1' => $request->boolean('objmk_n1'),
                'objmk_n2' => $request->boolean('objmk_n2'),
            ]);
        });

        return back()->with('status', 'Objective updated successfully.');
    }

    public function destroy(Kpi $kpi, KpiObjective $objective): RedirectResponse
    {
        $objective->delete();

        return back()->with('status', 'Objective deleted successfully.');
    }
}
