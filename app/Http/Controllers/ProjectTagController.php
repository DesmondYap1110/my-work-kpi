<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectTagRequest;
use App\Http\Requests\UpdateProjectTagRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\ProjectTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Project tags and their point weights - the settings that decide how much
 * each kind of work is worth when a project section is scored.
 */
class ProjectTagController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Settings', 'route' => '', 'active' => false],
            ['name' => 'Project Tags', 'route' => '', 'active' => true],
        ];
    }

    public function index(): View
    {
        return view('settings.project-tags.index');
    }

    public function store(StoreProjectTagRequest $request): RedirectResponse|JsonResponse
    {
        ProjectTag::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('status', 'Tag added successfully.');
    }

    public function update(UpdateProjectTagRequest $request, ProjectTag $projectTag): RedirectResponse|JsonResponse
    {
        $projectTag->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('status', 'Tag updated successfully.');
    }

    public function destroy(ProjectTag $projectTag): RedirectResponse
    {
        $projectTag->delete();

        return back()->with('status', 'Tag deleted successfully.');
    }
}
