<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\KpiCategory;
use App\Models\KpiObjectiveInfo;
use App\Models\KpiSetting;
use App\Models\ProjectTask;
use Illuminate\Support\Collection;

/**
 * The arithmetic of an appraisal, which is the arithmetic of a KPI score:
 *
 *   Projects        task marks earned in the review period, against the
 *                   position's target (or the work assigned)
 * + KPI objectives  the ratings given on this form
 * = KPI score out of 100, split by the position's Project KPI setting
 *
 * Two rules run through it. An unrated item counts for nothing on either side -
 * left out of the total and out of the maximum - so a group that does not apply
 * can simply be skipped. And a half with nothing to score is null, not zero: it
 * drops out and the other half stands alone, the same rule as
 * StaffKpiScoreService::blend().
 */
class AssessmentScoreService
{
    /**
     * Everything a form needs to render and total itself.
     *
     * @return array{
     *     objectives: array<string, mixed>,
     *     projects: array<string, mixed>,
     *     percentage: float|null,
     *     band: \App\Models\AssessmentBand|null
     * }
     */
    public function summary(Assessment $assessment): array
    {
        $objectives = $this->objectives($assessment);
        $projects = $this->projectMarks($assessment);

        // The same split, and the same rule, as the member's KPI score - the
        // position's Project KPI setting. See StaffKpiScoreService.
        $share = $this->projectShare($assessment);
        $points = StaffKpiScoreService::points($objectives['percentage'], $projects['percentage'], $share);
        $percentage = StaffKpiScoreService::blend($objectives['percentage'], $projects['percentage'], $share);

        return [
            'objectives' => $objectives + [
                'points' => $points['objectives'],
                'share' => 100 - (int) round($share * 100),
            ],
            'projects' => $projects + [
                'points' => $points['project'],
                'share' => (int) round($share * 100),
            ],
            'percentage' => $percentage,
            'band' => $assessment->template->bandFor($percentage),
        ];
    }

    /**
     * The KPI objectives, as the form prints them: category, objective, item,
     * mark.
     *
     * Rows come from the position pinned to the appraisal rather than the
     * member's current one, so a promotion mid-review does not rewrite a form
     * that has already been filled in.
     *
     * @return array{groups: array<int, array<string, mixed>>, earned: float, max: float, percentage: float|null}
     */
    public function objectives(Assessment $assessment): array
    {
        $scores = $assessment->scores->keyBy('objective_info_id');
        $groups = [];
        $earned = 0;
        $max = 0;

        foreach ($this->categoriesFor($assessment) as $category) {
            $rows = [];
            $groupEarned = 0;
            $groupMax = 0;
            $groupTotal = 0;

            foreach ($category->objectives as $objective) {
                foreach ($objective->infos as $info) {
                    $score = $scores->get($info->id);
                    $value = $score?->score();
                    // Each item's own best mark, as set on the position's KPI
                    // Setting page - an item marked 1-3 is out of 3, not 5.
                    $itemMax = $this->maxFor($info, $assessment);

                    $rows[] = [
                        'objective' => $objective->title,
                        'info' => $info,
                        'marks' => $this->marksFor($info, $assessment),
                        'employee_score' => $score?->employee_score,
                        'reviewer_score' => $score?->reviewer_score,
                    ];

                    // The printed maximum is every item, rated or not; the
                    // scored maximum counts only the ones that were rated.
                    $groupTotal += $itemMax;

                    if ($value !== null) {
                        $groupEarned += $value;
                        $groupMax += $itemMax;
                    }
                }
            }

            $groups[] = [
                'category' => $category,
                'rows' => $rows,
                'earned' => $groupEarned,
                'max' => $groupMax,
                'printed_max' => $groupTotal,
            ];

            $earned += $groupEarned;
            $max += $groupMax;
        }

        return [
            'groups' => $groups,
            'earned' => $earned,
            'max' => $max,
            'percentage' => $this->percentage($earned, $max),
        ];
    }

    /**
     * Project marks earned in the review period, measured the way the member's
     * KPI score measures them: against the position's target, or against the
     * work assigned when no target is set. Uses the position pinned to the
     * appraisal, so a later promotion does not rescore an old review.
     *
     * @return array{earned: float, assigned: float, target: float|null, percentage: float|null, tasks: Collection}
     */
    public function projectMarks(Assessment $assessment): array
    {
        $tasks = $assessment->period_from && $assessment->period_to && $assessment->staff
            ? app(ProjectDeliveryScoreService::class)->tasksFor($assessment->staff, $assessment->period_from->copy()->startOfDay(), $assessment->period_to->copy()->endOfDay())
            : collect();

        $earned = (float) $tasks->filter(fn (ProjectTask $task) => $task->status->isDone())->sum(fn (ProjectTask $task) => $task->points());
        $assigned = (float) $tasks->sum(fn (ProjectTask $task) => $task->points());
        $target = $assessment->position?->project_target;
        $target = $target !== null && (float) $target > 0 ? (float) $target : null;

        return [
            'earned' => round($earned, 2),
            'assigned' => round($assigned, 2),
            'target' => $target,
            'percentage' => ProjectDeliveryScoreService::percentage($earned, $assigned, $target),
            'tasks' => $tasks,
        ];
    }

    /**
     * Every measurement the member's position is rated on, as a flat list -
     * used to validate what comes back from the form.
     *
     * @return Collection<int, KpiObjectiveInfo>
     */
    public function measurementsFor(Assessment $assessment): Collection
    {
        return $this->categoriesFor($assessment)
            ->flatMap(fn (KpiCategory $category) => $category->objectives->flatMap->infos);
    }

    /**
     * The marks an item may be given, highest first: the item's own allowed
     * marks from the position's KPI Setting. An item with none cannot be rated.
     *
     * @return array<int, int>
     */
    public function marksFor(KpiObjectiveInfo $info, Assessment $assessment): array
    {
        return collect($info->allowed_marks ?? [])
            ->map(fn ($mark) => (int) $mark)
            ->filter(fn ($mark) => $mark > 0)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    /**
     * Records one measurement's marks, creating the row the first time it is
     * rated. Blank clears the mark rather than storing a zero - see
     * AssessmentScore::score().
     */
    /**
     * Records one side's mark and leaves the other side's alone - the member
     * owns the Employee column, the appraiser the Reviewer column.
     *
     * @param  'employee_score'|'reviewer_score'  $column
     */
    public function putMark(Assessment $assessment, int $infoId, string $column, ?int $mark): void
    {
        AssessmentScore::updateOrCreate(
            ['assessment_id' => $assessment->id, 'objective_info_id' => $infoId],
            [$column => $mark]
        );
    }

    private function maxFor(KpiObjectiveInfo $info, Assessment $assessment): int
    {
        return (int) (max($this->marksFor($info, $assessment) ?: [0]));
    }

    /**
     * @return Collection<int, KpiCategory>
     */
    private function categoriesFor(Assessment $assessment): Collection
    {
        return KpiCategory::query()
            ->with(['objectives' => fn ($q) => $q->with('infos')])
            ->where('position_id', $assessment->position_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Projects' share of the score, 0.0 - 1.0: the appraised position's own
     * figure, else the company figure.
     */
    private function projectShare(Assessment $assessment): float
    {
        $own = $assessment->position?->project_weight;

        return $own !== null
            ? max(0, min(100, (int) $own)) / 100
            : KpiSetting::current()->projectShare();
    }

    private function percentage(float $earned, float $max): ?float
    {
        return $max > 0 ? round(($earned / $max) * 100, 2) : null;
    }
}
