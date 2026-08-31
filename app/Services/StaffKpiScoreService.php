<?php

namespace App\Services;

use App\Enums\ObjectiveType;
use App\Enums\ProjectKpiStatus;
use App\Enums\ProjectStatus;
use App\Models\Staff;
use Illuminate\Support\Collection;

/**
 * Encapsulates the KPI scoring rules from the legacy member-viewkpi.php
 * page, kept out of controllers/Blade so they stay testable in one place.
 */
class StaffKpiScoreService
{
    /**
     * Highest achievable mark for one completed project, for this staff
     * member's position: the sum of each standard objective's best allowed
     * mark, plus a flat +2 if the KPI has any "extra" (bonus) objective.
     */
    public function maxMarkPerProject(Staff $staff): int
    {
        $kpi = $staff->position?->kpi;

        if (! $kpi) {
            return 0;
        }

        $objectives = $kpi->objectives()->with('mark')->get();

        $standardMax = $objectives
            ->filter(fn ($objective) => $objective->obj_type === ObjectiveType::Standard)
            ->sum(fn ($objective) => $objective->maxMark());

        $hasBonus = $objectives->contains(fn ($objective) => $objective->obj_type === ObjectiveType::Extra);

        return $standardMax + ($hasBonus ? 2 : 0);
    }

    /**
     * Completed projects belonging to the staff member's team — the only
     * projects that ever carry scored project_kpi rows.
     */
    public function completedTeamProjects(Staff $staff): Collection
    {
        if (! $staff->team) {
            return collect();
        }

        return $staff->team->projects()->status(ProjectStatus::Completed)->get();
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
        $projectIds = $this->completedTeamProjects($staff)->pluck('project_id');

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
}
