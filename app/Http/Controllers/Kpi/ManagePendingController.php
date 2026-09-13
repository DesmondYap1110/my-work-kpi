<?php

namespace App\Http\Controllers\Kpi;

use App\Enums\ProjectKpiStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kpi\RejectProjectKpiRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\ProjectKpi;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ManagePendingController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'KPI', 'route' => '', 'active' => false],
            ['name' => 'Manage Pending', 'route' => '', 'active' => true],
        ];
    }

    public function index(): View
    {
        return view('manage-pending.index');
    }

    public function approve(ProjectKpi $projectKpi): RedirectResponse
    {
        $projectKpi->update(['status' => ProjectKpiStatus::Approved]);

        return back()->with('status', 'KPI entry approved.');
    }

    public function reject(RejectProjectKpiRequest $request, ProjectKpi $projectKpi): RedirectResponse
    {
        $projectKpi->update([
            'status' => ProjectKpiStatus::Rejected,
            'mark' => $request->integer('mark'),
        ]);

        return back()->with('status', 'KPI entry rejected.');
    }
}
