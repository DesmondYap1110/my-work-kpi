<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StorePositionRequest;
use App\Http\Requests\Hr\UpdatePositionRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\StaffPosition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PositionController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Human Resource', 'route' => '', 'active' => false],
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
        if ($position->isAdministrator()) {
            return $this->refuseAdministrator($request);
        }

        $position->update($request->validated());


        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('status', 'Position updated successfully.');
    }

    public function destroy(Request $request, StaffPosition $position): RedirectResponse|JsonResponse
    {
        if ($position->isAdministrator()) {
            return $this->refuseAdministrator($request);
        }

        // Objectives, not hasKpi(): a position can have objectives with no
        // items yet, which reads as "no KPI" - but deleting it would still
        // leave those objectives behind with no position to belong to.
        if ($position->objectives()->exists()) {
            return back()->withErrors(['position' => 'This position still has KPI objectives. Remove the KPI first before deleting the position.']);
        }

        $position->delete();

        return back()->with('status', 'Position deleted successfully.');
    }

    /**
     * The Administrator position is the portal's access gate: renaming or
     * deleting it would lock people out. The list offers neither action, so
     * reaching here means a hand-made request.
     */
    private function refuseAdministrator(Request $request): RedirectResponse|JsonResponse
    {
        $message = 'The Administrator position is built in and cannot be changed.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->withErrors(['position' => $message]);
    }
}
