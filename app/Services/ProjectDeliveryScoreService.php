<?php

namespace App\Services;

use App\Models\ProjectTask;
use App\Models\Staff;

/**
 * Turns delivered project work into a score.
 *
 * project_tag.points has always described itself as "what turn delivery into a
 * score", but nothing read it - a KPI came entirely from self-rated objectives,
 * and shipping work counted for nothing. This is the consumer.
 *
 * A person is measured against the work they were given, not against a target
 * somebody has to configure:
 *
 *     earned   = points of their tasks finished inside the period
 *     assigned = points of every task of theirs due in the period
 *     score    = earned / assigned
 *
 * Someone with no assigned work scores null, not zero. Nothing to measure is
 * not the same as doing nothing, and the blend in StaffKpiScoreService relies
 * on that distinction to fall back to objectives alone.
 */
class ProjectDeliveryScoreService
{
    /**
     * @return array{earned: float, assigned: float, target: float|null, percentage: float|null, done: int, total: int}
     */
    public function forStaff(Staff $staff, $from, $to): array
    {
        $tasks = $this->tasksFor($staff, $from, $to);

        $assigned = $tasks->sum(fn (ProjectTask $task) => $task->points());
        $done = $tasks->filter(fn (ProjectTask $task) => $task->status->isDone());
        $earned = $done->sum(fn (ProjectTask $task) => $task->points());

        $target = $staff->position?->project_target;
        $target = $target !== null && (float) $target > 0 ? (float) $target : null;

        return [
            'earned' => round($earned, 2),
            'assigned' => round($assigned, 2),
            'target' => $target,
            'percentage' => self::percentage($earned, $assigned, $target),
            'done' => $done->count(),
            'total' => $tasks->count(),
            // The rows behind the numbers, for the scorecard to list.
            'tasks' => $tasks,
        ];
    }

    /**
     * The tasks that count towards a member's project marks in a period.
     *
     * In scope if due in the period, or finished in it - work delivered early
     * still counts, and an overdue task still counts against the period it
     * belonged to.
     *
     * @return \Illuminate\Support\Collection<int, ProjectTask>
     */
    public function tasksFor(Staff $staff, $from, $to): \Illuminate\Support\Collection
    {
        return ProjectTask::query()
            ->with(['tag', 'project'])
            ->where('assignee_id', $staff->id)
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('due_date', [$from, $to])
                    ->orWhereBetween('completed_at', [$from, $to]);
            })
            ->orderByDesc('completed_at')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * How much of the project part of a KPI was achieved, 0-100.
     *
     * With a target - "a Tester needs 30 marks" - it is earned against that
     * target, capped at 100: 24 of 30 is 80%. Blended into the KPI at the
     * position's project share, 80% of a 50% share is 40 of the 100.
     *
     * Without one, earned against the points of the tasks they were given,
     * as before. And with neither a target nor any assigned points there is
     * nothing to measure, which is null rather than zero - the blend then
     * scores on objectives alone.
     *
     * Public and static so the rule can be tested on its own.
     */
    public static function percentage(float $earned, float $assigned, ?float $target): ?float
    {
        if ($target !== null && $target > 0) {
            return round(min(100, ($earned / $target) * 100), 2);
        }

        return $assigned > 0 ? round(($earned / $assigned) * 100, 2) : null;
    }
}
