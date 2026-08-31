<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectKpi;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\Team;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard.index', [
            'pendingKpiCount' => ProjectKpi::pending()->count(),
            'positionsWithoutKpiCount' => StaffPosition::withoutKpi()->count(),
            'activeTeamsCount' => Team::active()->count(),
            'activeMembersCount' => Staff::active()->excludingAdmin()->count(),
            'totalProjectsCount' => Project::count(),
        ]);
    }
}
