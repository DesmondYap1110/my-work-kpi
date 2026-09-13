<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kpi\StoreKpiObjectiveItemRequest;
use App\Http\Requests\Kpi\UpdateKpiObjectiveItemRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\KpiObjective;
use App\Models\KpiObjectiveInfo;
use App\Models\StaffPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The scored items under one objective. Each carries its own allowed marks
 * and the Standard/Extra flag that drives scoring.
 */
class KpiObjectiveItemController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Position', 'route' => 'positions.index', 'active' => false],
            ['name' => 'Objective Items', 'route' => '', 'active' => true],
        ];
    }

    public function index(StaffPosition $position, KpiObjective $objective): View
    {
        return view('kpi.objectives.items.index', [
            'position' => $position,
            'objective' => $objective,
        ]);
    }

    public function store(StoreKpiObjectiveItemRequest $request, StaffPosition $position, KpiObjective $objective): RedirectResponse
    {
        $objective->infos()->create($this->attributes($request));

        return back()->with('status', 'Item added successfully.');
    }

    public function update(UpdateKpiObjectiveItemRequest $request, StaffPosition $position, KpiObjective $objective, KpiObjectiveInfo $item): RedirectResponse
    {
        $item->update($this->attributes($request));

        return back()->with('status', 'Item updated successfully.');
    }

    public function destroy(StaffPosition $position, KpiObjective $objective, KpiObjectiveInfo $item): RedirectResponse
    {
        $item->delete();

        return back()->with('status', 'Item deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(Request $request): array
    {
        return [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            // The inputs post the mark values themselves; duplicates are
            // folded and the order preserved.
            'allowed_marks' => array_values(array_unique(
                array_map('intval', $request->input('allowed_marks', []))
            )),
        ];
    }
}
