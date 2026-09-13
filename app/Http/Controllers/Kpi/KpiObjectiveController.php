<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kpi\StoreKpiObjectiveRequest;
use App\Http\Requests\Kpi\UpdateKpiObjectiveRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\KpiCategory;
use App\Models\KpiObjective;
use App\Models\StaffPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            ['name' => 'KPI Setting', 'route' => '', 'active' => true],
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
            // appear (with its own "add objective" button). Scoped to this
            // position - categories are not shared between jobs.
            'categories' => KpiCategory::forPosition($position->id)
                ->with('section')
                ->orderBy('sort_order')->orderBy('name')->get(),
            'objectivesByCategory' => $objectives,
            // Which part of the appraisal form a category is rated under. A
            // company that has added a part needs somewhere to point headings
            // at it, or the part renders empty.
            // For the weighting box: the company figure the position follows
            // when it has no figure of its own.
            'companyProjectWeight' => (int) \App\Models\KpiSetting::current()->project_weight,
            // The project tags this position can use (its own and the ones open
            // to everyone), and the other positions' tags it could be added to.
            'positionTags' => \App\Models\ProjectTag::active()->withCount('tasks')
                ->orderBy('sort_order')->orderBy('name')->get()
                ->filter(fn ($tag) => $tag->allowsPosition($position->id))->values(),
            'otherTags' => \App\Models\ProjectTag::active()
                ->orderBy('name')->get()
                ->reject(fn ($tag) => $tag->allowsPosition($position->id))->values(),
            'positionNames' => StaffPosition::pluck('position_name', 'id'),
            'sections' => \App\Models\AssessmentSection::query()
                ->where('template_id', \App\Models\AssessmentTemplate::current()->id)
                ->where('type', \App\Models\AssessmentSection::TYPE_RATING)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function store(StoreKpiObjectiveRequest $request, StaffPosition $position): RedirectResponse
    {
        $position->objectives()->create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category_id' => $this->resolveCategory($request, $position),
        ]);

        return back()->with('status', 'Objective added successfully.');
    }

    public function update(UpdateKpiObjectiveRequest $request, StaffPosition $position, KpiObjective $objective): RedirectResponse
    {
        $objective->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category_id' => $this->resolveCategory($request, $position),
        ]);

        return back()->with('status', 'Objective updated successfully.');
    }

    public function destroy(StaffPosition $position, KpiObjective $objective): RedirectResponse
    {
        // Its items go with it - see KpiObjective::booted().
        $items = $objective->infos()->count();
        $objective->delete();

        return back()->with('status', $items > 0
            ? 'Objective deleted, along with its '.$items.' '.Str::plural('item', $items).'.'
            : 'Objective deleted successfully.');
    }

    /**
     * An existing category, none, or a new one named in the form.
     *
     * A chosen category is checked against this position before it is
     * accepted, so a tampered form cannot file an objective under another
     * job's heading.
     */
    private function resolveCategory(Request $request, StaffPosition $position): ?int
    {
        $value = $request->input('category_id');

        if (blank($value)) {
            return null;
        }

        if ($value !== StoreKpiObjectiveRequest::NEW) {
            return KpiCategory::forPosition($position->id)
                ->whereKey((int) $value)
                ->value('id');
        }

        return KpiCategory::firstOrCreate([
            'position_id' => $position->id,
            'name' => trim($request->input('category_name')),
        ])->id;
    }
}
