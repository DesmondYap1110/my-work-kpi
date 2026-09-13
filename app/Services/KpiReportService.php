<?php

namespace App\Services;

use App\Enums\ProjectKpiStatus;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\KpiObjective;
use App\Models\KpiSetting;
use App\Models\Project;
use App\Models\ProjectKpi;
use App\Models\ProjectTask;
use App\Models\Staff;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The numbers behind KPI > Report.
 *
 * Every figure follows the same rules as a member's own KPI page
 * (StaffKpiScoreService::finalScore), but is worked out for a whole group of
 * members at once: a handful of grouped queries per period instead of several
 * queries per member. That is what keeps a team of 30, or a 12-month trend
 * across the company, to tens of queries rather than thousands.
 *
 *   project marks   tasks due or finished in the period, priced by tag points
 *   KPI objectives  approved marks on projects completed in the period
 *   KPI score       the two blended at the position's project share
 */
class KpiReportService
{
    public function __construct(private readonly StaffKpiScoreService $kpi)
    {
    }

    /**
     * Each member's KPI score for a period, keyed by staff id.
     *
     * @param  Collection<int, Staff>  $members  with position loaded
     * @return Collection<int, array<string, mixed>>
     */
    public function scores(Collection $members, Carbon $from, Carbon $to): Collection
    {
        if ($members->isEmpty()) {
            return collect();
        }

        $ids = $members->pluck('id')->all();
        $delivery = $this->deliveryTotals($ids, $from, $to);
        $completed = $this->completedProjectPairs($ids, $from, $to);
        $approved = $this->approvedMarks($ids, $completed);
        $maxPerProject = $this->maxMarkPerPosition($members);
        $companyShare = $this->companyShare ??= KpiSetting::current()->projectShare();

        return $members->mapWithKeys(function (Staff $staff) use ($delivery, $completed, $approved, $maxPerProject, $companyShare) {
            // Project marks - ProjectDeliveryScoreService::forStaff().
            $d = $delivery->get($staff->id, ['earned' => 0.0, 'assigned' => 0.0, 'done' => 0, 'total' => 0]);
            $target = $staff->position?->project_target;
            $target = $target !== null && (float) $target > 0 ? (float) $target : null;
            $deliveryPct = ProjectDeliveryScoreService::percentage($d['earned'], $d['assigned'], $target);

            // KPI objectives - StaffKpiScoreService::totalScore().
            $projects = count($completed[$staff->id] ?? []);
            $max = ($maxPerProject[$staff->position_id] ?? 0) * $projects;
            $mark = (int) ($approved[$staff->id] ?? 0);
            $objectivePct = $max > 0 ? round(($mark / $max) * 100, 2) : null;

            // The position's own share wins - StaffKpiScoreService::projectShare().
            $own = $staff->position?->project_weight;
            $weight = $own !== null ? max(0, min(100, (int) $own)) / 100 : $companyShare;
            $points = StaffKpiScoreService::points($objectivePct, $deliveryPct, $weight);

            return [$staff->id => [
                'staff' => $staff,
                'percentage' => StaffKpiScoreService::blend($objectivePct, $deliveryPct, $weight),
                'project' => $deliveryPct,
                'objective' => $objectivePct,
                'project_points' => $points['project'],
                'project_share' => $points['project_share'],
                'objective_points' => $points['objectives'],
                'objective_share' => $points['objectives_share'],
                'earned' => round($d['earned'], 2),
                'assigned' => round($d['assigned'], 2),
                'target' => $target,
                'tasks_done' => $d['done'],
                'tasks_total' => $d['total'],
                'mark' => $mark,
                'max_mark' => $max,
                'projects' => $projects,
            ]];
        });
    }

    /**
     * KPI Summary: the group's averages, over the members who have a score.
     * A member with nothing to score is left out rather than counted as zero.
     *
     * @return array<string, mixed>
     */
    public function summary(Collection $scores): array
    {
        $avg = fn (string $key) => ($values = $scores->pluck($key)->filter(fn ($v) => $v !== null))->isEmpty()
            ? null
            : round($values->avg(), 2);

        return [
            'members' => $scores->count(),
            'scored' => $scores->filter(fn ($s) => $s['percentage'] !== null)->count(),
            'percentage' => $avg('percentage'),
            'project' => $avg('project'),
            'objective' => $avg('objective'),
            'tasks_done' => (int) $scores->sum('tasks_done'),
            'tasks_total' => (int) $scores->sum('tasks_total'),
            'earned' => round((float) $scores->sum('earned'), 2),
        ];
    }

    /**
     * Performance Trend: the group's average scores month by month.
     *
     * @return array<int, array{label: string, percentage: float|null, project: float|null, objective: float|null}>
     */
    public function trend(Collection $members, Carbon $from, Carbon $to): array
    {
        $months = [];
        $cursor = $from->copy()->startOfMonth();
        $end = $to->copy()->startOfMonth();

        // A period shorter than a quarter still gets a trend worth reading:
        // the six months up to its end.
        if ($cursor->diffInMonths($end) < 2) {
            $cursor = $end->copy()->subMonths(5);
        }

        // Capped, so a very long custom range cannot run away.
        while ($cursor->lte($end) && count($months) < 24) {
            $summary = $this->summary($this->scores($members, $cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth()));

            $months[] = [
                'label' => $cursor->format('M Y'),
                'percentage' => $summary['percentage'],
                'project' => $summary['project'],
                'objective' => $summary['objective'],
            ];

            $cursor->addMonth();
        }

        return $months;
    }

    /**
     * Project Report: per project, the work in the period - tasks finished,
     * points earned, and how much of it is complete.
     *
     * @param  array<int, int>|null  $memberIds  null for every assignee
     * @return Collection<int, array<string, mixed>>
     */
    public function projects(?array $memberIds, Carbon $from, Carbon $to): Collection
    {
        $rows = $this->tasksInPeriod($memberIds, $from, $to)
            ->groupBy('project_task.project_id')
            ->selectRaw('project_task.project_id,
                COUNT(*) AS total,
                SUM(project_task.status = ?) AS done,
                COALESCE(SUM(project_tag.points), 0) AS assigned,
                COALESCE(SUM(CASE WHEN project_task.status = ? THEN project_tag.points END), 0) AS earned',
                [TaskStatus::Done->value, TaskStatus::Done->value])
            ->toBase()
            ->get();

        $projects = Project::withTrashed()->whereIn('id', $rows->pluck('project_id'))->get(['id', 'title', 'status', 'deleted_at'])->keyBy('id');

        return $rows->map(fn ($row) => [
            'project' => $projects->get($row->project_id),
            'total' => (int) $row->total,
            'done' => (int) $row->done,
            'assigned' => round((float) $row->assigned, 2),
            'earned' => round((float) $row->earned, 2),
            'rate' => $row->total > 0 ? round($row->done / $row->total * 100, 1) : null,
        ])->sortByDesc('earned')->values();
    }

    /**
     * Task Breakdown: points by work category (project tag).
     *
     * @param  array<int, int>|null  $memberIds
     * @return Collection<int, array<string, mixed>>
     */
    public function tags(?array $memberIds, Carbon $from, Carbon $to): Collection
    {
        return $this->tasksInPeriod($memberIds, $from, $to)
            ->groupBy('project_task.tag_id', 'project_tag.name')
            ->selectRaw('project_task.tag_id, project_tag.name,
                COUNT(*) AS total,
                SUM(project_task.status = ?) AS done,
                COALESCE(SUM(project_tag.points), 0) AS assigned,
                COALESCE(SUM(CASE WHEN project_task.status = ? THEN project_tag.points END), 0) AS earned',
                [TaskStatus::Done->value, TaskStatus::Done->value])
            ->toBase()
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name ?? 'No tag',
                'total' => (int) $row->total,
                'done' => (int) $row->done,
                'assigned' => round((float) $row->assigned, 2),
                'earned' => round((float) $row->earned, 2),
            ])
            ->sortByDesc('earned')
            ->values();
    }

    /**
     * KPI Objective Report: target against actual for each objective.
     *
     * Target is the most that could have been earned - every item's best mark
     * on every project the member completed in the period. Actual is the
     * approved marks. The same numbers the objectives half of the score uses.
     *
     * @param  Collection<int, Staff>  $members
     * @return Collection<int, array<string, mixed>>
     */
    public function objectives(Collection $members, Carbon $from, Carbon $to): Collection
    {
        if ($members->isEmpty()) {
            return collect();
        }

        $ids = $members->pluck('id')->all();
        $completed = $this->completedProjectPairs($ids, $from, $to);

        // Completed projects per position, since the objectives are the position's.
        $projectsByPosition = [];
        foreach ($members as $staff) {
            $projectsByPosition[$staff->position_id] = ($projectsByPosition[$staff->position_id] ?? 0) + count($completed[$staff->id] ?? []);
        }

        // Approved marks per item, only on the projects that count.
        $marks = [];
        if ($completed !== []) {
            ProjectKpi::query()
                ->whereIn('staff_id', array_keys($completed))
                ->whereIn('project_id', collect($completed)->flatten()->unique()->all())
                ->where('status', ProjectKpiStatus::Approved)
                ->get(['staff_id', 'project_id', 'objective_info_id', 'mark'])
                ->each(function ($row) use ($completed, &$marks) {
                    if (in_array($row->project_id, $completed[$row->staff_id] ?? [], true)) {
                        $marks[$row->objective_info_id] = ($marks[$row->objective_info_id] ?? 0) + (int) $row->mark;
                    }
                });
        }

        return KpiObjective::query()
            ->with(['position', 'category', 'infos'])
            ->whereIn('position_id', array_keys($projectsByPosition))
            ->orderBy('position_id')->orderBy('category_id')->orderBy('id')
            ->get()
            ->map(function (KpiObjective $objective) use ($projectsByPosition, $marks) {
                $projects = $projectsByPosition[$objective->position_id] ?? 0;
                $target = $objective->infos->sum(fn ($item) => $item->maxMark()) * $projects;
                $actual = $objective->infos->sum(fn ($item) => $marks[$item->id] ?? 0);

                return [
                    'position' => $objective->position->position_name ?? '-',
                    'category' => $objective->category->name ?? 'Uncategorised',
                    'objective' => $objective->title ?? 'Untitled objective',
                    'items' => $objective->infos->count(),
                    'target' => (int) $target,
                    'actual' => (int) $actual,
                    'rate' => $target > 0 ? round($actual / $target * 100, 1) : null,
                ];
            })
            ->filter(fn ($row) => $row['items'] > 0)
            ->values();
    }

    /**
     * Tasks due or finished in the period, joined to their tag's points - the
     * same scope as ProjectDeliveryScoreService::tasksFor(). A deleted tag
     * keeps its points on the work already tagged with it.
     */
    private function tasksInPeriod(?array $memberIds, Carbon $from, Carbon $to)
    {
        return ProjectTask::query()
            ->leftJoin('project_tag', 'project_tag.id', '=', 'project_task.tag_id')
            ->when($memberIds !== null, fn ($q) => $q->whereIn('project_task.assignee_id', $memberIds))
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('project_task.due_date', [$from, $to])
                    ->orWhereBetween('project_task.completed_at', [$from, $to]);
            });
    }

    /**
     * @return Collection<int, array{earned: float, assigned: float, done: int, total: int}>
     */
    private function deliveryTotals(array $ids, Carbon $from, Carbon $to): Collection
    {
        return $this->tasksInPeriod($ids, $from, $to)
            ->groupBy('project_task.assignee_id')
            ->selectRaw('project_task.assignee_id,
                COUNT(*) AS total,
                SUM(project_task.status = ?) AS done,
                COALESCE(SUM(project_tag.points), 0) AS assigned,
                COALESCE(SUM(CASE WHEN project_task.status = ? THEN project_tag.points END), 0) AS earned',
                [TaskStatus::Done->value, TaskStatus::Done->value])
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->assignee_id => [
                'earned' => (float) $row->earned,
                'assigned' => (float) $row->assigned,
                'done' => (int) $row->done,
                'total' => (int) $row->total,
            ]]);
    }

    /**
     * Completed projects each member had a task on, completed in the period -
     * StaffKpiScoreService::completedProjectsFor(), for many members at once.
     *
     * @return array<int, array<int, int>>  staff id => project ids
     */
    private function completedProjectPairs(array $ids, Carbon $from, Carbon $to): array
    {
        $pairs = [];

        Project::query()
            ->join('project_task', 'project_task.project_id', '=', 'project.id')
            ->whereNull('project_task.deleted_at')
            ->whereIn('project_task.assignee_id', $ids)
            ->where('project.status', ProjectStatus::Completed)
            ->whereRaw('COALESCE(project.complete_date, project.updated_at) BETWEEN ? AND ?', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->distinct()
            ->toBase()
            ->get(['project_task.assignee_id', 'project.id'])
            ->each(function ($row) use (&$pairs) {
                $pairs[(int) $row->assignee_id][] = (int) $row->id;
            });

        return $pairs;
    }

    /**
     * @param  array<int, array<int, int>>  $completed
     * @return array<int, int>  staff id => approved mark
     */
    private function approvedMarks(array $ids, array $completed): array
    {
        if ($completed === []) {
            return [];
        }

        $totals = [];

        ProjectKpi::query()
            ->whereIn('staff_id', $ids)
            ->whereIn('project_id', collect($completed)->flatten()->unique()->all())
            ->where('status', ProjectKpiStatus::Approved)
            ->groupBy('staff_id', 'project_id')
            ->selectRaw('staff_id, project_id, SUM(mark) AS mark')
            ->toBase()
            ->get()
            ->each(function ($row) use ($completed, &$totals) {
                if (in_array((int) $row->project_id, $completed[$row->staff_id] ?? [], true)) {
                    $totals[(int) $row->staff_id] = ($totals[(int) $row->staff_id] ?? 0) + (int) $row->mark;
                }
            });

        return $totals;
    }

    /**
     * The best mark per completed project, once per position rather than once
     * per member - it depends only on the position.
     *
     * @return array<int, int>
     */
    private function maxMarkPerPosition(Collection $members): array
    {
        // Remembered for the request: a 12-month trend asks for the same
        // positions twelve times.
        foreach ($members->unique('position_id') as $staff) {
            $this->maxMarks[$staff->position_id] ??= $this->kpi->maxMarkPerProject($staff);
        }

        return $this->maxMarks;
    }

    /** @var array<int, int> position id => best mark per project */
    private array $maxMarks = [];

    private ?float $companyShare = null;
}
