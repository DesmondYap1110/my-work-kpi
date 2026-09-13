<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\ProjectKpi;
use App\Models\ProjectTask;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\Team;
use App\Services\AppraisalScheduleService;
use App\Services\StaffKpiScoreService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Two dashboards, because the question differs with who is asking.
     *
     * The administrator's tiles count the company - members, teams, projects,
     * KPIs awaiting approval - and every one of them links into a page only an
     * administrator may open. A staff member gets the same layout answering
     * "where do I stand and what is on my plate".
     */
    public function __invoke(Request $request, StaffKpiScoreService $scoreService): View
    {
        $staff = $request->user();

        return $staff->isAdmin()
            ? view('dashboard.index', $this->adminTiles())
            : view('dashboard.index', $this->staffTiles($staff, $scoreService));
    }

    /**
     * @return array<string, mixed>
     */
    private function adminTiles(): array
    {
        return [
            'isAdmin' => true,
            'pendingKpiCount' => ProjectKpi::pending()->count(),
            'positionsWithoutKpiCount' => StaffPosition::withoutKpi()->count(),
            'activeTeamsCount' => Team::active()->count(),
            'activeMembersCount' => Staff::active()->excludingAdmin()->count(),
            'totalProjectsCount' => Project::count(),
            // Members whose appraisal is overdue or due within a week.
            'appraisalsDue' => app(AppraisalScheduleService::class)->attention(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function staffTiles(Staff $staff, StaffKpiScoreService $scoreService): array
    {
        $mine = ProjectTask::where('assignee_id', $staff->id);

        return [
            'isAdmin' => false,
            'myScore' => $scoreService->finalScore($staff)['percentage'],
            // "Open" is everything not yet finished, which is what a person
            // still has to do - Blocked work included, since it is still theirs.
            'openTaskCount' => (clone $mine)->where('status', '!=', TaskStatus::Done)->count(),
            'overdueTaskCount' => (clone $mine)
                ->where('status', '!=', TaskStatus::Done)
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', today())
                ->count(),
            'doneTaskCount' => (clone $mine)->done()->count(),
        ];
    }
}
