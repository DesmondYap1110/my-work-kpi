<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kpi\StoreKpiCategoryRequest;
use App\Http\Requests\Kpi\UpdateKpiCategoryRequest;
use App\Models\KpiCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * Categories group a position's objectives - "Soft Skill", "Service".
 *
 * Each belongs to one position, so a restaurant role is never offered a
 * developer role's headings. Only the name is editable after creation.
 */
class KpiCategoryController extends Controller
{
    public function store(StoreKpiCategoryRequest $request): RedirectResponse
    {
        KpiCategory::create($request->validated());

        return back()->with('status', 'Category added successfully.');
    }

    public function update(UpdateKpiCategoryRequest $request, KpiCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return back()->with('status', 'Category updated successfully.');
    }

    public function destroy(KpiCategory $category): RedirectResponse
    {
        // The objectives and their items go with it - see KpiCategory::booted().
        $objectives = $category->objectives()->count();
        $category->delete();

        return back()->with('status', $objectives > 0
            ? 'Category deleted, along with its '.$objectives.' '.Str::plural('objective', $objectives).'.'
            : 'Category deleted successfully.');
    }
}
