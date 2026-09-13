<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentProjectScore;
use App\Models\AssessmentScore;
use App\Models\AssessmentSection;
use App\Models\KpiCategory;
use App\Models\KpiObjectiveInfo;
use App\Models\ProjectTask;
use Illuminate\Support\Collection;

/**
 * The arithmetic of an appraisal: what each part scored, and what the whole
 * form comes to.
 *
 * Two rules run through all of it.
 *
 * An unrated row counts for nothing on either side - it is left out of the
 * total and out of the maximum. That is what makes the form's "(if
 * applicable)" groups work without a flag: skip Leadership and the member is
 * neither rewarded nor punished for it, and the remaining groups still add up
 * to a fair percentage.
 *
 * A part with nothing in it scores null, not zero, and drops out of the
 * weighting - the other part then stands alone at its full value. A member
 * with no project work in the period is scored on soft skills, not scored down
 * for projects nobody gave them. This is the same rule the KPI blend uses; see
 * StaffKpiScoreService::blend().
 */
class AssessmentScoreService
{
    /**
     * Everything a form needs to render and total itself.
     *
     * @return array{
     *     sections: array<int, array<string, mixed>>,
     *     percentage: float|null,
     *     band: \App\Models\AssessmentBand|null
     * }
     */
    public function summary(Assessment $assessment): array
    {
        $sections = [];

        foreach ($assessment->template->sections as $section) {
            $sections[] = $section->isProject()
                ? $this->projectSection($assessment, $section)
                : $this->ratingSection($assessment, $section);
        }

        $percentage = $this->overall($sections);

        return [
            'sections' => $sections,
            'percentage' => $percentage,
            'band' => $assessment->template->bandFor($percentage),
        ];
    }

    /**
     * The rated half, as the form prints it: heading, objective, item, mark.
     *
     * Rows come from the position pinned to the appraisal rather than the
     * member's current one, so a promotion mid-review does not rewrite a form
     * that has already been filled in.
     *
     * @return array<string, mixed>
     */
    public function ratingSection(Assessment $assessment, AssessmentSection $section): array
    {
        $scores = $assessment->scores->keyBy('objective_info_id');
        // The top of the scale comes from the template, not from each item's
        // own allowed_marks - those belong to the older self-scoring flow and
        // still say 1-5. A company that moves its form to a six-point scale
        // must have its maximums move with it, or a perfect form reads 120%.
        $maxMark = $this->maxRating($assessment);
        $groups = [];
        $earned = 0;
        $max = 0;

        foreach ($this->categoriesFor($assessment, $section) as $category) {
            $rows = [];
            $groupEarned = 0;
            $groupMax = 0;
            $groupTotal = 0;

            foreach ($category->objectives as $objective) {
                foreach ($objective->infos as $info) {
                    $score = $scores->get($info->id);
                    $value = $score?->score();

                    $rows[] = [
                        'objective' => $objective->title,
                        'info' => $info,
                        'employee_score' => $score?->employee_score,
                        'reviewer_score' => $score?->reviewer_score,
                    ];

                    // The printed "/30" is every item, rated or not; the
                    // scored maximum counts only the ones that were rated.
                    $groupTotal += $maxMark;

                    if ($value !== null) {
                        $groupEarned += $value;
                        $groupMax += $maxMark;
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
            'section' => $section,
            'groups' => $groups,
            'rows' => [],
            'earned' => $earned,
            'max' => $max,
            'percentage' => $this->percentage($earned, $max),
        ];
    }

    /**
     * Part 2, whose rows are the member's own work rather than a fixed list.
     *
     * @return array<string, mixed>
     */
    public function projectSection(Assessment $assessment, AssessmentSection $section): array
    {
        $maxRating = $this->maxRating($assessment);
        $rows = $assessment->projectScores;

        $rated = $rows->filter(fn (AssessmentProjectScore $row) => $row->score() !== null);
        $earned = $rated->sum(fn (AssessmentProjectScore $row) => $row->score());
        $max = $rated->count() * $maxRating;

        return [
            'section' => $section,
            'groups' => [],
            'rows' => $rows,
            'earned' => $earned,
            'max' => $max,
            'printed_max' => $rows->count() * $maxRating,
            'percentage' => $this->percentage($earned, $max),
        ];
    }

    /**
     * Part 2's rows, drawn from the work the member actually did in the period
     * the appraiser chose.
     *
     * One row per project, not per task: the form asks about a body of work,
     * and a row per task would be unreadable on a busy quarter. The line is
     * written out in full and stored, so a project renamed or deleted later
     * does not change what a finished appraisal says.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function projectRowsFor(Assessment $assessment): Collection
    {
        if (! $assessment->period_from || ! $assessment->period_to) {
            return collect();
        }

        return ProjectTask::query()
            ->with('project')
            ->where('assignee_id', $assessment->staff_id)
            ->where(function ($query) use ($assessment) {
                // Matches ProjectDeliveryScoreService: due in the period, or
                // finished in it.
                $query->whereBetween('due_date', [$assessment->period_from, $assessment->period_to])
                    ->orWhereBetween('completed_at', [$assessment->period_from, $assessment->period_to]);
            })
            ->get()
            ->groupBy('project_id')
            ->map(function (Collection $tasks, $projectId) {
                $done = $tasks->filter(fn (ProjectTask $task) => $task->status->isDone())->count();
                $title = $tasks->first()->project->title ?? 'Project';

                return [
                    'project_id' => $projectId,
                    'description' => $title.' - '.$done.' of '.$tasks->count().' '
                        .\Illuminate\Support\Str::plural('task', $tasks->count()).' completed',
                ];
            })
            ->values();
    }

    /**
     * Fills Part 2 in from the member's work, replacing whatever was there.
     *
     * Called when the period changes, so the rows always describe the period
     * being judged. Ratings already given are carried across by project, so
     * moving the end date by a week does not throw away the appraiser's work.
     */
    public function syncProjectRows(Assessment $assessment): void
    {
        $existing = $assessment->projectScores()->get()->keyBy('project_id');
        $rows = $this->projectRowsFor($assessment);

        $assessment->projectScores()->delete();

        foreach ($rows as $row) {
            $previous = $existing->get($row['project_id']);

            AssessmentProjectScore::create([
                'assessment_id' => $assessment->id,
                'project_id' => $row['project_id'],
                'description' => $row['description'],
                'employee_score' => $previous?->employee_score,
                'reviewer_score' => $previous?->reviewer_score,
            ]);
        }

        $assessment->load('projectScores');
    }

    /**
     * Every measurement the member's position is rated on, as a flat list -
     * used to validate what comes back from the form.
     *
     * @return Collection<int, KpiObjectiveInfo>
     */
    public function measurementsFor(Assessment $assessment): Collection
    {
        return KpiCategory::query()
            ->with('objectives.infos')
            ->where('position_id', $assessment->position_id)
            ->get()
            ->flatMap(fn (KpiCategory $category) => $category->objectives->flatMap->infos);
    }

    /**
     * @return Collection<int, KpiCategory>
     */
    private function categoriesFor(Assessment $assessment, AssessmentSection $section): Collection
    {
        return KpiCategory::query()
            ->with(['objectives' => fn ($q) => $q->with('infos')])
            ->where('position_id', $assessment->position_id)
            ->where(function ($query) use ($section) {
                // A category added before the form existed has no section.
                // Rather than vanish from the appraisal, it is rated under the
                // first rating part - which is where the seeder puts them too.
                $query->where('section_id', $section->id);

                if ($section->sort_order <= 1) {
                    $query->orWhereNull('section_id');
                }
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * The top of the scale, read from the template rather than assumed to be
     * five - a company may rate out of ten.
     */
    private function maxRating(Assessment $assessment): int
    {
        return (int) ($assessment->template->ratings->max('value') ?: 5);
    }

    private function percentage(float $earned, float $max): ?float
    {
        return $max > 0 ? round(($earned / $max) * 100, 2) : null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     */
    private function overall(array $sections): ?float
    {
        return self::combine(array_map(
            fn (array $part) => [$part['percentage'], $part['section']->weight()],
            $sections
        ));
    }

    /**
     * The rule that turns the parts into one number, kept public and static so
     * it can be tested on its own - it decides what somebody's review says, so
     * every edge case in it is a decision rather than an implementation detail.
     *
     * Weights are divided by what actually counted rather than by 100. A form
     * of 50 + 50 where one part has nothing to score still reads out of 100:
     * the part that was scored stands alone at its full value. A member given
     * no project work in the period is judged on soft skills, not marked down
     * for projects nobody assigned them.
     *
     * @param  array<int, array{0: float|null, 1: float}>  $parts  [percentage, weight]
     */
    public static function combine(array $parts): ?float
    {
        $weighted = 0.0;
        $weight = 0.0;

        foreach ($parts as [$percentage, $partWeight]) {
            // Null is "nothing to score", which is not the same as zero and
            // must not drag the other parts down.
            if ($percentage === null || $partWeight <= 0) {
                continue;
            }

            $weighted += $percentage * $partWeight;
            $weight += $partWeight;
        }

        return $weight > 0 ? round($weighted / $weight, 2) : null;
    }

    /**
     * Records one measurement's marks, creating the row the first time it is
     * rated. Blank clears the mark rather than storing a zero - see
     * AssessmentScore::score().
     */
    public function putScore(Assessment $assessment, int $infoId, ?int $employee, ?int $reviewer): void
    {
        AssessmentScore::updateOrCreate(
            ['assessment_id' => $assessment->id, 'objective_info_id' => $infoId],
            ['employee_score' => $employee, 'reviewer_score' => $reviewer]
        );
    }
}
