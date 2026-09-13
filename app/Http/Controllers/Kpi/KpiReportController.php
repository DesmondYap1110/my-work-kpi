<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\AssessmentTemplate;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\Team;
use App\Services\KpiReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * KPI > Report: how the company, a team, a position or one member is
 * performing over a period - summary, monthly trend, projects, objectives,
 * members compared, and points by kind of work.
 *
 * All six read the same filters, so narrowing to a team narrows every report.
 * The arithmetic lives in KpiReportService.
 */
class KpiReportController extends Controller implements BreadcrumbInterfaces
{
    public const PERIODS = [
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'this_quarter' => 'This quarter',
        'this_year' => 'This year',
        'last_year' => 'Last year',
        'custom' => 'Custom',
    ];

    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'KPI', 'route' => '', 'active' => false],
            ['name' => 'Report', 'route' => '', 'active' => true],
        ];
    }

    public function index(Request $request, KpiReportService $reports): View
    {
        [$period, $from, $to] = $this->period($request);

        $members = Staff::active()->excludingAdmin()
            ->with(['position', 'team'])
            ->when($request->filled('team_id'), fn ($q) => $q->where('team_id', $request->integer('team_id')))
            ->when($request->filled('position_id'), fn ($q) => $q->where('position_id', $request->integer('position_id')))
            ->when($request->filled('staff_id'), fn ($q) => $q->whereKey($request->integer('staff_id')))
            ->orderBy('staff_name')
            ->get();

        $scores = $reports->scores($members, $from, $to);
        $filtered = $request->hasAny(['team_id', 'position_id', 'staff_id']) && collect($request->only(['team_id', 'position_id', 'staff_id']))->filter()->isNotEmpty();
        // Unfiltered, project and tag figures include every assignee, so work
        // done by someone since deactivated still shows against its project.
        $memberIds = $filtered ? $members->pluck('id')->all() : null;

        $bands = AssessmentTemplate::current()->load('bands');

        return view('kpi.report.index', [
            'periods' => self::PERIODS,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'teams' => Team::orderBy('team_name')->pluck('team_name', 'id'),
            'positions' => StaffPosition::excludingAdmin()->orderBy('position_name')->pluck('position_name', 'id'),
            'allMembers' => Staff::active()->excludingAdmin()->with('team')->orderBy('staff_name')->get(['id', 'staff_name', 'team_id']),
            'summary' => $reports->summary($scores),
            'trend' => $reports->trend($members, $from, $to),
            'projects' => $reports->projects($memberIds, $from, $to),
            'objectives' => $reports->objectives($members, $from, $to),
            'ranking' => $scores->sortByDesc(fn ($s) => $s['percentage'] ?? -1)->values()
                ->map(fn ($s) => $s + ['band' => $bands->bandFor($s['percentage'])]),
            'tags' => $reports->tags($memberIds, $from, $to),
        ]);
    }

    /**
     * @return array{0: string, 1: Carbon, 2: Carbon}
     */
    private function period(Request $request): array
    {
        $period = array_key_exists($request->input('period'), self::PERIODS) ? $request->input('period') : 'this_year';

        if ($period === 'custom') {
            try {
                $from = Carbon::createFromFormat('Y-m-d', (string) $request->input('from'))->startOfDay();
                $to = Carbon::createFromFormat('Y-m-d', (string) $request->input('to'))->endOfDay();

                return $from->lte($to) ? [$period, $from, $to] : [$period, $to->copy()->startOfDay(), $from->copy()->endOfDay()];
            } catch (\Throwable) {
                $period = 'this_year';
            }
        }

        return match ($period) {
            'this_month' => [$period, now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [$period, now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_quarter' => [$period, now()->startOfQuarter(), now()->endOfQuarter()],
            'last_year' => [$period, now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            default => ['this_year', now()->startOfYear(), now()->endOfYear()],
        };
    }
}
