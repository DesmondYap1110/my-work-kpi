<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\AssessmentTemplate;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\Team;
use App\Services\KpiReportService;
use App\Support\Csv;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * KPI > Report: how the company, a team, a position or one member is
 * performing over a period - summary, monthly trend, projects, teams and
 * members compared, and points by kind of work.
 *
 * All of them read the same filters, so narrowing to a team narrows every report.
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
            ['name' => 'KPI Report', 'route' => '', 'active' => true],
        ];
    }

    public const EXPORTS = [
        'members' => 'Member ranking',
        'teams' => 'Team performance',
        'projects' => 'Project report',
        'tags' => 'Task breakdown',
        'trend' => 'Performance trend',
    ];

    public function index(Request $request, KpiReportService $reports): View
    {
        [$period, $from, $to, $members, $memberIds] = $this->scope($request);

        $scores = $reports->scores($members, $from, $to);

        $tags = $reports->tags($memberIds, $from, $to);
        $bands = AssessmentTemplate::current()->load('bands');
        $ranking = $scores->sortByDesc(fn ($s) => $s['percentage'] ?? -1)->values()
            ->map(fn ($s) => $s + ['band' => $bands->bandFor($s['percentage'])]);

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
            'ranking' => $ranking,
            'teamRows' => $teamRows = $reports->teams($scores, $bands),
            // Chart series, shaped here rather than in the view: one bar per
            // team, its average project + objective points.
            'teamChart' => $teamRows->whereNotNull('percentage')->values()->map(fn ($t) => [
                'name' => $t['team'],
                'project' => $t['project_points'] ?? 0,
                'objective' => $t['objective_points'] ?? 0,
            ]),
            'tags' => $tags,
            'tagChart' => $tags->where('earned', '>', 0)->values(),
            'exports' => self::EXPORTS,
        ]);
    }

    /**
     * One report table as CSV, with the same filters and figures as the page.
     */
    public function export(Request $request, KpiReportService $reports): StreamedResponse
    {
        $section = array_key_exists($request->input('section'), self::EXPORTS) ? $request->input('section') : 'members';
        [, $from, $to, $members, $memberIds] = $this->scope($request);

        $num = fn ($n) => $n === null ? null : round((float) $n, 2);
        $filename = 'kpi-report-'.str_replace('_', '-', $section).'-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';
        $bands = AssessmentTemplate::current()->load('bands');

        [$headings, $rows] = match ($section) {
            'teams' => [
                ['Team', 'Avg. KPI score', 'Avg. project %', 'Avg. objectives %', 'Avg. project points', 'Avg. objective points', 'Top performer', 'Top score', 'Members', 'Members scored', 'Status'],
                $reports->teams($reports->scores($members, $from, $to), $bands)->map(fn ($t) => [
                    $t['team'], $num($t['percentage']), $num($t['project']), $num($t['objective']),
                    $num($t['project_points']), $num($t['objective_points']),
                    $t['top']['staff']->staff_name ?? null, $num($t['top']['percentage'] ?? null),
                    $t['members'], $t['scored'], $t['band']->label ?? null,
                ]),
            ],
            'projects' => [
                ['Project', 'Status', 'Tasks completed', 'Tasks total', 'Points earned', 'Points assigned', 'Completion rate %'],
                $reports->projects($memberIds, $from, $to)->map(fn ($p) => [
                    $p['project']->title ?? 'Deleted project', $p['project']?->status->label(),
                    $p['done'], $p['total'], $num($p['earned']), $num($p['assigned']), $num($p['rate']),
                ]),
            ],
            'tags' => [
                ['Tag', 'Tasks completed', 'Tasks total', 'Points earned', 'Points assigned'],
                $reports->tags($memberIds, $from, $to)->map(fn ($t) => [
                    $t['name'], $t['done'], $t['total'], $num($t['earned']), $num($t['assigned']),
                ]),
            ],
            'trend' => [
                ['Month', 'Avg. KPI score', 'Avg. project %', 'Avg. objectives %'],
                collect($reports->trend($members, $from, $to))->map(fn ($m) => [
                    $m['label'], $num($m['percentage']), $num($m['project']), $num($m['objective']),
                ]),
            ],
            default => [
                ['Rank', 'Member', 'Email', 'Team', 'Position', 'Project %', 'Project points', 'Project share', 'Objectives %', 'Objective points', 'Objective share', 'KPI score', 'Band', 'Tasks completed', 'Tasks total', 'Task points earned', 'Appraisals'],
                $reports->scores($members, $from, $to)->sortByDesc(fn ($s) => $s['percentage'] ?? -1)->values()
                    ->map(fn ($s, $i) => [
                        $s['percentage'] === null ? null : $i + 1,
                        $s['staff']->staff_name, $s['staff']->email,
                        $s['staff']->team->team_name ?? null, $s['staff']->position->position_name ?? null,
                        $num($s['project']), $num($s['project_points']), $num($s['project_share']),
                        $num($s['objective']), $num($s['objective_points']), $num($s['objective_share']),
                        $num($s['percentage']), $bands->bandFor($s['percentage'])->label ?? null,
                        $s['tasks_done'], $s['tasks_total'], $num($s['earned']), $s['appraisals'],
                    ]),
            ],
        };

        return Csv::download($filename, $headings, $rows);
    }

    /**
     * The period and members the filters select - shared by the page and its
     * exports, so a download always matches what is on screen.
     *
     * @return array{0: string, 1: Carbon, 2: Carbon, 3: \Illuminate\Support\Collection, 4: array<int, int>|null}
     */
    private function scope(Request $request): array
    {
        [$period, $from, $to] = $this->period($request);

        $members = Staff::active()->excludingAdmin()
            ->with(['position', 'team'])
            ->when($request->filled('team_id'), fn ($q) => $q->where('team_id', $request->integer('team_id')))
            ->when($request->filled('position_id'), fn ($q) => $q->where('position_id', $request->integer('position_id')))
            ->when($request->filled('staff_id'), fn ($q) => $q->whereKey($request->integer('staff_id')))
            ->orderBy('staff_name')
            ->get();

        $filtered = collect($request->only(['team_id', 'position_id', 'staff_id']))->filter()->isNotEmpty();
        // Unfiltered, project and tag figures include every assignee, so work
        // done by someone since deactivated still shows against its project.
        $memberIds = $filtered ? $members->pluck('id')->all() : null;

        return [$period, $from, $to, $members, $memberIds];
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
