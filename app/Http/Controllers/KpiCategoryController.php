<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKpiCategoryRequest;
use App\Http\Requests\UpdateKpiCategoryRequest;
use App\Models\KpiCategory;
use Illuminate\Http\RedirectResponse;

/**
 * Categories group objectives - "Soft Skill", "Technical Skill". They are
 * shared across positions, so this only creates and renames them; which
 * objectives sit under them is decided per position.
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
        // Objectives survive; they simply fall back to "uncategorised".
        $category->objectives()->update(['category_id' => null]);
        $category->delete();

        return back()->with('status', 'Category deleted. Its objectives are now uncategorised.');
    }
}
