<?php

namespace App\Services;

use App\Enums\ProjectKpiStatus;
use App\Enums\ProjectStatus;
use App\Models\KpiSetting;
use App\Models\Project;
use App\Models\Staff;
use Illuminate\Support\Collection;

/**
 * Encapsulates the KPI scoring rules from the legacy member-viewkpi.php
 * page, kept out of controllers/Blade so they stay testable in one place.
 *
 * A score has two halves:
 *
 *   objectives - what this class has always computed: marks the staff member
 *                submitted against their position's scored items, approved by
 *                a manager.
 *   delivery   - project work actually finished, priced by its tag's points.
 *                See ProjectDeliveryScoreService.
 *
 * finalScore() blends them at the company's configured weight.
 */
class StaffKpiScoreService
{
    public function __construct(
        private readonly ProjectDeliveryScoreService $delivery,
    ) {
    }

    /**
     * Highest achievable mark for one completed project, for this staff
     * member's position: the sum of every scored item's best allowed mark.
     *
     * The legacy app split items into Standard and Extra, adding a flat +2
     * when any Extra existed. That flag is gone - an item worth more than the
     * others simply allows higher marks, which the sum already reflects.
     */
    public function maxMarkPerProject(Staff $staff): int
    {
        $position = $staff->position;

        if (! $position || ! $position->hasKpi()) {
            return 0;
        }

        // The scored items hang off each objective and carry the mark range.
        return (int) $position->objectives()->with('infos')->get()
            ->flatMap->infos
            ->sum(fn ($item) => $item->maxMark());
    }

    /**
     * Completed projects this staff member worked on — the only projects that
     * ever carry scored project_kpi rows for them.
     *
     * "Worked on" means they were assigned a task. It used to mean "belongs to
     * my team", which counted every colleague's project as theirs; a project
     * has no team of its own any more.
     */
    public function completedProjectsFor(Staff $staff): Collection
    {
        return Project::query()
            ->status(ProjectStatus::Completed)
            ->whereHas('tasks', fn ($q) => $q->where('assignee_id', $staff->id))
            ->get();
    }

    /**
     * Total approved mark and percentage across the staff member's completed
     * team projects, optionally scoped to a single project. Rejected/pending
     * rows are intentionally excluded from the total (only approved marks
     * count), per the corrected scoring semantics for this rewrite.
     *
     * @return array{total_mark: int, max_possible: int, percentage: float}
     */
    public function totalScore(Staff $staff, ?int $projectId = null): array
    {
        $maxPerProject = $this->maxMarkPerProject($staff);

        // pluck('id'): the projects table's key was renamed from project_id,
        // and this still asked for the old name - so it collected nulls, matched
        // no project_kpi rows, and every approved mark silently scored zero.
        $projectIds = $this->completedProjectsFor($staff)->pluck('id');

        if ($projectId) {
            $projectIds = $projectIds->filter(fn ($id) => $id === $projectId);
        }

        $approvedMark = (int) $staff->projectKpis()
            ->whereIn('project_id', $projectIds)
            ->where('status', ProjectKpiStatus::Approved)
            ->sum('mark');

        $maxPossible = $maxPerProject * $projectIds->count();

        return [
            'total_mark' => $approvedMark,
            'max_possible' => $maxPossible,
            'percentage' => $maxPossible > 0 ? round(($approvedMark / $maxPossible) * 100, 2) : 0.0,
        ];
    }

    /**
     * Delivery's share of this staff member's score, 0.0 - 1.0.
     *
     * The position's own weight wins when set, so a role with no project work
     * can sit at 0 while the rest of the company is at 70.
     */
    public function projectShare(Staff $staff): float
    {
        $override = $staff->position?->project_weight;

        if ($override !== null) {
            return max(0, min(100, (int) $override)) / 100;
        }

        return KpiSetting::current()->projectShare();
    }

    /**
     * The blended score.
     *
     * Either half can be absent, and absent is not zero:
     *   - no assigned project work (or the company runs no projects) -> the
     *     objective score stands alone.
     *   - no objectives on the position -> delivery stands alone.
     *   - neither -> null, because there is nothing to score.
     *
     * This is what lets a company with no projects use the app unchanged
     * without special-casing anything.
     *
     * @return array{objective: float|null, delivery: float|null, weight: float, percentage: float|null, delivery_detail: array}
     */
    public function finalScore(Staff $staff, $from = null, $to = null): array
    {
        $from ??= now()->startOfYear();
        $to ??= now()->endOfYear();

        $objectiveScore = $this->totalScore($staff);
        $objective = $objectiveScore['max_possible'] > 0 ? $objectiveScore['percentage'] : null;

        $deliveryDetail = $this->delivery->forStaff($staff, $from, $to);
        $delivery = $deliveryDetail['percentage'];

        $weight = $this->projectShare($staff);
        $points = self::points($objective, $delivery, $weight);

        return [
            'objective' => $objective,
            'delivery' => $delivery,
            'weight' => $weight,
            'percentage' => self::blend($objective, $delivery, $weight),
            'delivery_detail' => $deliveryDetail,
            'objective_detail' => $objectiveScore,
            // What each half is worth out of 100, and how much of the 100 it
            // was allowed - see points().
            'project_points' => $points['project'],
            'project_share' => $points['project_share'],
            'objective_points' => $points['objectives'],
            'objective_share' => $points['objectives_share'],
            'from' => $from,
            'to' => $to,
        ];
    }

    /**
     * Each half's contribution to the score out of 100, worked out exactly as
     * blend() works out the total, so the two parts on a scorecard always add
     * up to the total printed beside them.
     *
     * Normally the project half is worth its share and the objectives the
     * rest: 80% on projects at a 50 share is 40 points. When one half has
     * nothing to score, blend() lets the other stand alone, so that half's
     * share becomes the whole 100 here too.
     *
     * @return array{project: float|null, project_share: float, objectives: float|null, objectives_share: float}
     */
    public static function points(?float $objective, ?float $delivery, float $weight): array
    {
        [$objective, $delivery] = self::excludeUncounted($objective, $delivery, $weight);

        $projectShare = match (true) {
            $delivery === null => 0.0,
            $objective === null => 100.0,
            default => round($weight * 100, 2),
        };

        $objectivesShare = $objective === null ? 0.0 : round(100 - $projectShare, 2);

        return [
            'project' => $delivery === null ? null : round($delivery * $projectShare / 100, 2),
            'project_share' => $projectShare,
            'objectives' => $objective === null ? null : round($objective * $objectivesShare / 100, 2),
            'objectives_share' => $objectivesShare,
        ];
    }

    /**
     * The KPI objectives behind the objectives half, item by item: approved
     * marks against the most that could have been earned, across the member's
     * completed projects - or one of them.
     *
     * Only approved marks count towards the score; pending and rejected ones
     * are counted separately so the scorecard can say what is still waiting.
     *
     * @return \Illuminate\Support\Collection<int, array{category: string, objectives: \Illuminate\Support\Collection}>
     */
    public function objectiveBreakdown(Staff $staff, ?int $projectId = null): Collection
    {
        $position = $staff->position;

        if (! $position) {
            return collect();
        }

        $projectIds = $this->completedProjectsFor($staff)->pluck('id');

        if ($projectId) {
            $projectIds = $projectIds->filter(fn ($id) => $id === $projectId)->values();
        }

        $entries = $staff->projectKpis()
            ->whereIn('project_id', $projectIds)
            ->get()
            ->groupBy('objective_info_id');

        return $position->objectives()
            ->with(['category', 'infos'])
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($objective) => $objective->category->name ?? 'Uncategorised')
            ->map(fn ($objectives, $category) => [
                'category' => $category,
                'objectives' => $objectives->map(fn ($objective) => [
                    'title' => $objective->title,
                    'items' => $objective->infos->map(function ($item) use ($entries, $projectIds) {
                        $rows = $entries->get($item->id, collect());

                        return [
                            'title' => $item->title,
                            'approved' => (int) $rows->where('status', ProjectKpiStatus::Approved)->sum('mark'),
                            'max' => $item->maxMark() * $projectIds->count(),
                            'pending' => $rows->filter(fn ($r) => $r->status === null && $r->submitted_at !== null)->count(),
                            'rejected' => $rows->where('status', ProjectKpiStatus::Rejected)->count(),
                        ];
                    }),
                ]),
            ])
            ->values();
    }

    /**
     * The blending rule itself, kept public and static so it can be tested
     * directly - it is the heart of the score and every edge case here is a
     * decision, not an implementation detail.
     *
     * $weight is delivery's share, 0.0 - 1.0.
     */
    public static function blend(?float $objective, ?float $delivery, float $weight): ?float
    {
        [$objective, $delivery] = self::excludeUncounted($objective, $delivery, $weight);

        if ($delivery === null && $objective === null) {
            return null;
        }

        if ($delivery === null) {
            return $objective;
        }

        if ($objective === null) {
            return $delivery;
        }

        return round($delivery * $weight + $objective * (1 - $weight), 2);
    }

    /**
     * Drops a half whose share of the score is zero, before anything else
     * looks at it.
     *
     * Without this, the "one half stands alone" rule let an uncounted half
     * become the whole score: a position with a project share of 0 and no
     * approved objective marks yet scored 100 out of 100 purely on project
     * work the company had said should count for nothing.
     *
     * @return array{0: float|null, 1: float|null}  [objective, delivery]
     */
    private static function excludeUncounted(?float $objective, ?float $delivery, float $weight): array
    {
        if ($weight <= 0) {
            $delivery = null;
        }

        if ($weight >= 1) {
            $objective = null;
        }

        return [$objective, $delivery];
    }
}
