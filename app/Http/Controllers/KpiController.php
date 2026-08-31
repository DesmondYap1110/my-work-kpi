<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKpiRequest;
use App\Http\Requests\UpdateKpiRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\Kpi;
use App\Models\StaffPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KpiController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'KPI', 'route' => '', 'active' => false],
            ['name' => 'Manage KPI', 'route' => '', 'active' => true],
        ];
    }

    public function index(): View
    {
        return view('kpi.index', [
            // Only positions without a KPI template yet can receive a new one.
            'availablePositions' => StaffPosition::withoutKpi()->orderBy('position_name')->get(),
        ]);
    }

    public function store(StoreKpiRequest $request): RedirectResponse
    {
        $kpi = Kpi::create($request->validated());
        $kpi->position()->update(['kpistatus' => true]);

        return back()->with('status', 'KPI template added successfully.');
    }

    public function update(UpdateKpiRequest $request, Kpi $kpi): RedirectResponse
    {
        $kpi->update($request->validated());

        return back()->with('status', 'KPI template updated successfully.');
    }

    public function destroy(Kpi $kpi): RedirectResponse
    {
        $kpi->position()->update(['kpistatus' => false]);
        $kpi->delete();

        return back()->with('status', 'KPI template deleted successfully.');
    }
}
