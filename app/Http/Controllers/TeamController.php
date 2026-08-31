<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeamController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Team', 'route' => '', 'active' => true],
        ];
    }

    public function index(): View
    {
        return view('teams.index');
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        Team::create($request->validated() + ['team_status' => true]);

        return back()->with('status', 'Team added successfully.');
    }

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $team->update($request->validated());

        return back()->with('status', 'Team updated successfully.');
    }

    public function toggleStatus(Team $team): RedirectResponse
    {
        $team->update(['team_status' => ! $team->team_status]);

        return back()->with('status', 'Team status updated successfully.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        if ($team->hasActiveStaff()) {
            return back()->withErrors(['team' => 'This team still has active members. Reassign or block them first before deleting the team.']);
        }

        $team->delete();

        return back()->with('status', 'Team deleted successfully.');
    }
}
