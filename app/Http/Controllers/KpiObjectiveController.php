<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKpiObjectiveRequest;
use App\Http\Requests\UpdateKpiObjectiveRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\KpiCategory;
use App\Models\KpiObjective;
use App\Models\StaffPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The whole KPI structure for one position, on one page:
 *
 *     category -> objective -> measurable items
 *
 * Everything is added in place, so building a position's KPI never means
 * leaving the screen.
 */
class KpiObjectiveController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Position', 'route' => 'positions.index', 'active' => false],
            ['name' => 'KPI Objectives', 'route' => '', 'active' => true],
        ];
    }

    public function index(StaffPosition $position): View|RedirectResponse
    {
        // The Administrator position is the portal's access gate, not a
        // reviewed job - it has no KPI to manage.
        if ($position->isAdministrator()) {
            return redirect()->route('positions.index')
                ->withErrors(['position' => 'The Administrator position is not assessed.']);
        }

        $objectives = $position->objectives()
            ->with('infos')
            ->orderBy('id')
            ->get()
            ->groupBy('category_id');

        return view('kpi.objectives.index', [
            'position' => $position,
            // Driven by the categories, not the objectives: a category is
            // created first and then filled, so an empty one still has to
            // appear (with its own "add objective" button).
            'categories' => KpiCategory::orderBy('name')->get(),
            'objectivesByCategory' => $objectives,
        ]);
    }

    public function store(StoreKpiObjectiveRequest $request, StaffPosition $position): RedirectResponse
    {
        $position->objectives()->create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category_id' => $this->resolveCategory($request),
        ]);

        return back()->with('status', 'Objective added successfully.');
    }

    public function update(UpdateKpiObjectiveRequest $request, StaffPosition $position, KpiObjective $objective): RedirectResponse
    {
        $objective->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category_id' => $this->resolveCategory($request),
        ]);

        return back()->with('status', 'Objective updated successfully.');
    }

    public function destroy(StaffPosition $position, KpiObjective $objective): RedirectResponse
    {
        // Its items cascade with it at the database level.
        $objective->delete();

        return back()->with('status', 'Objective deleted successfully.');
    }

    /**
     * An existing category, none, or a new one named in the form.
     */
    private function resolveCategory(Request $request): ?int
    {
        $value = $request->input('category_id');

        if (blank($value)) {
            return null;
        }

        if ($value !== StoreKpiObjectiveRequest::NEW) {
            return (int) $value;
        }

        return KpiCategory::firstOrCreate(
            ['name' => trim($request->input('category_name'))]
        )->id;
    }
}
