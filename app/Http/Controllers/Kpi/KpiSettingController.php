<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\KpiSetting;
use App\Models\StaffPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * How much of a KPI score comes from delivering project work.
 *
 * One number for the company, with a per-position override for roles the
 * company answer does not fit - an office admin in a project-driven firm.
 */
class KpiSettingController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Project Setup', 'route' => '', 'active' => false],
            ['name' => 'Weighting', 'route' => '', 'active' => true],
        ];
    }

    public function edit(): View
    {
        return view('kpi-settings.edit', [
            'setting' => KpiSetting::current(),
            'positions' => StaffPosition::excludingAdmin()->orderBy('position_name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_weight' => ['required', 'integer', 'between:0,100'],
            // Blank means "follow the company setting", which is why these are
            // nullable rather than defaulted to the company number.
            'positions' => ['array'],
            'positions.*' => ['nullable', 'integer', 'between:0,100'],
        ], [
            'project_weight.between' => 'The company weighting must be between 0 and 100.',
            'positions.*.between' => 'A position weighting must be between 0 and 100.',
        ]);

        KpiSetting::current()->update(['project_weight' => $validated['project_weight']]);

        foreach ($validated['positions'] ?? [] as $positionId => $weight) {
            StaffPosition::excludingAdmin()
                ->whereKey($positionId)
                ->update(['project_weight' => $weight === null || $weight === '' ? null : (int) $weight]);
        }

        return back()->with('status', 'Weighting updated successfully.');
    }
}
