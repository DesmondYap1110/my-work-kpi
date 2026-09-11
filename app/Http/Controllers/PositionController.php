<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\StaffPosition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PositionController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Position', 'route' => '', 'active' => true],
        ];
    }

    public function index(): View
    {
        return view('positions.index');
    }

    public function store(StorePositionRequest $request): RedirectResponse|JsonResponse
    {
        StaffPosition::create($request->validated());


        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('status', 'Position added successfully.');
    }

    public function update(UpdatePositionRequest $request, StaffPosition $position): RedirectResponse|JsonResponse
    {
        $position->update($request->validated());


        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('status', 'Position updated successfully.');
    }

    public function destroy(StaffPosition $position): RedirectResponse
    {
        if ($position->kpistatus) {
            return back()->withErrors(['position' => 'This position has a KPI template assigned. Remove the KPI first before deleting the position.']);
        }

        $position->delete();

        return back()->with('status', 'Position deleted successfully.');
    }
}
