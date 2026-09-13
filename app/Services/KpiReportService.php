<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\KpiSetting;
use App\Models\Project;
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
 *   KPI objectives  appraiser marks on generated appraisals covering the period
 *   KPI score       the two blended at the position's project share
 */
class KpiReportService
{
    public function __construct(private readonly AssessmentScoreService $appraisals)
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
        // Every generated appraisal covering the period, for all members in one
        // query; each is totalled once and remembered - see objectivesAcross().
        $appraisals = $this->appraisals->generatedOverlapping($ids, $from, $to)->groupBy('staff_id');
        $companyShare = $this->companyShare ??= KpiSetting::current()->projectShare();

        return $members->mapWithKeys(function (Staff $staff) use ($delivery, $appraisals, $companyShare) {
            // Project marks - ProjectDeliveryScoreService::forStaff().
            $d = $delivery->get($staff->id, ['earned' => 0.0, 'assigned' => 0.0, 'done' => 0, 'total' => 0]);
            $target = $staff->position?->project_target;
            $target = $target !== null && (float) $target > 0 ? (float) $target : null;
            $deliveryPct = ProjectDeliveryScoreService::percentage($d['earned'], $d['assigned'], $target);

            // KPI objectives - StaffKpiScoreService::objectiveScore().
            $o = $this->appraisals->objectivesAcross($appraisals->get($staff->id, collect()));
            $objectivePct = $o['percentage'];

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
                'mark' => $o['earned'],
                'max_mark' => $o['max'],
                'appraisals' => $o['appraisals'],
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

        $scored = $scores->filter(fn ($s) => $s['percentage'] !== null);

        return [
            'members' => $scores->count(),
            'scored' => $scored->count(),
            // The best and weakest scored member - null when nobody was scored.
            'highest' => $scored->sortByDesc('percentage')->first(),
            'lowest' => $scored->sortBy('percentage')->first(),
            'percentage' => $avg('percentage'),
            'project' => $avg('project'),
            'objective' => $avg('objective'),
            'tasks_done' => (int) $scores->sum('tasks_done'),
            'tasks_total' => (int) $scores->sum('tasks_total'),
            'earned' => round((float) $scores->sum('earned'), 2),
        ];
    }

    /**
     * Team Performance, one row per team: average KPI score over the members
     * who were scored, head count, and the performance band that average
     * falls in. Members without a team are grouped as "No team".
     *
     * @param  Collection<int, array<string, mixed>>  $scores
     * @return Collection<int, array<string, mixed>>
     */
    public function teams(Collection $scores, \App\Models\AssessmentTemplate $template): Collection
    {
        return $scores
            ->groupBy(fn ($s) => $s['staff']->team_id ?? 0)
            ->map(function (Collection $rows) use ($template) {
                $summary = $this->summary($rows);

                // Average points of each half over the scored members, so the two
                // stacked bars add up to the team's average KPI score.
                $scored = $rows->filter(fn ($s) => $s['percentage'] !== null);

                return [
                    'team' => $rows->first()['staff']->team->team_name ?? 'No team',
                    // The team's highest KPI score, and whose it is.
                    'top' => $scored->sortByDesc('percentage')->first(),
                    'project_points' => $scored->isEmpty() ? null : round($scored->avg(fn ($s) => $s['project_points'] ?? 0), 2),
                    'objective_points' => $scored->isEmpty() ? null : round($scored->avg(fn ($s) => $s['objective_points'] ?? 0), 2),
                    'members' => $summary['members'],
                    'scored' => $summary['scored'],
                    'percentage' => $summary['percentage'],
                    'project' => $summary['project'],
                    'objective' => $summary['objective'],
                    'band' => $template->bandFor($summary['percentage']),
                ];
            })
            ->sortByDesc(fn ($row) => $row['percentage'] ?? -1)
            ->values();
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

    private ?float $companyShare = null;
}
