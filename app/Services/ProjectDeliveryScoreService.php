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
     * @return array{earned: float, assigned: float, percentage: float|null, done: int, total: int}
     */
    public function forStaff(Staff $staff, $from, $to): array
    {
        $tasks = ProjectTask::query()
            ->with('tag')
            ->where('assignee_id', $staff->id)
            ->where(function ($query) use ($from, $to) {
                // In scope if it was due in the period, or finished in it -
                // work delivered early still counts, and an overdue task
                // still counts against the period it belonged to.
                $query->whereBetween('due_date', [$from, $to])
                    ->orWhereBetween('completed_at', [$from, $to]);
            })
            ->get();

        $assigned = $tasks->sum(fn (ProjectTask $task) => $task->points());
        $done = $tasks->filter(fn (ProjectTask $task) => $task->status->isDone());
        $earned = $done->sum(fn (ProjectTask $task) => $task->points());

        return [
            'earned' => round($earned, 2),
            'assigned' => round($assigned, 2),
            // No assigned points at all - either no tasks, or none of them
            // carry a tag worth anything. Either way there is nothing to score.
            'percentage' => $assigned > 0 ? round(($earned / $assigned) * 100, 2) : null,
            'done' => $done->count(),
            'total' => $tasks->count(),
        ];
    }
}
