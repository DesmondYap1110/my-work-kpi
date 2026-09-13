<?php

namespace Tests\Feature;

use App\Services\ProjectDeliveryScoreService;
use App\Services\StaffKpiScoreService;
use Tests\TestCase;

/**
 * A position's project marks target, and how it becomes part of a KPI score.
 *
 * The worked example these are built on: a Tester must earn 30 marks from
 * projects, and the KPI is split 50 project / 50 objectives - both adjustable.
 */
class ProjectKpiTargetTest extends TestCase
{
    public function test_marks_are_earned_against_the_target(): void
    {
        $this->assertSame(80.0, ProjectDeliveryScoreService::percentage(24, 10, 30));
    }

    /**
     * Doing more than the role asks does not push the project part past its
     * share of the KPI.
     */
    public function test_beating_the_target_caps_at_full_marks(): void
    {
        $this->assertSame(100.0, ProjectDeliveryScoreService::percentage(45, 50, 30));
    }

    /**
     * With a target set there is always something to measure: a Tester who
     * earned nothing has scored nothing, not "no project work to judge".
     */
    public function test_nothing_earned_against_a_target_is_zero_not_null(): void
    {
        $this->assertSame(0.0, ProjectDeliveryScoreService::percentage(0, 0, 30));
    }

    /**
     * The target is what matters, not how much work they happened to be given:
     * being assigned only 10 marks of tasks and finishing them all is still
     * 10 of the 30 the role requires.
     */
    public function test_the_target_ignores_how_much_work_was_assigned(): void
    {
        $this->assertSame(33.33, ProjectDeliveryScoreService::percentage(10, 10, 30));
    }

    public function test_without_a_target_it_measures_against_the_work_assigned(): void
    {
        $this->assertSame(50.0, ProjectDeliveryScoreService::percentage(5, 10, null));
        $this->assertNull(ProjectDeliveryScoreService::percentage(0, 0, null));
    }

    public function test_a_zero_target_is_treated_as_no_target(): void
    {
        $this->assertSame(50.0, ProjectDeliveryScoreService::percentage(5, 10, 0));
    }

    /**
     * The whole example: 24 of 30 project marks is 80% of the 50 project share
     * (40), and 80% on objectives is 80% of the other 50 (40) - 80 out of 100.
     */
    public function test_the_tester_example_comes_to_eighty_out_of_a_hundred(): void
    {
        $project = ProjectDeliveryScoreService::percentage(24, 0, 30);
        $objectives = 80.0;

        $this->assertSame(80.0, StaffKpiScoreService::blend($objectives, $project, 0.5));
    }

    /**
     * The scorecard prints project points + objective points = score, so the
     * parts must always add up to the total blend() gives - including when one
     * half has nothing to score yet.
     */
    public function test_the_two_parts_always_add_up_to_the_total(): void
    {
        foreach ([[80.0, 80.0, 0.5], [50.0, 100.0, 0.7], [null, 80.0, 0.5], [80.0, null, 0.5], [60.0, 20.0, 0.0], [60.0, 20.0, 1.0]] as [$objective, $delivery, $weight]) {
            $points = StaffKpiScoreService::points($objective, $delivery, $weight);
            $sum = round(($points['project'] ?? 0) + ($points['objectives'] ?? 0), 2);

            $this->assertSame(StaffKpiScoreService::blend($objective, $delivery, $weight), $sum,
                'Parts do not add up for objective='.var_export($objective, true).' delivery='.var_export($delivery, true).' weight='.$weight);
        }
    }

    public function test_the_tester_example_splits_forty_and_forty(): void
    {
        $points = StaffKpiScoreService::points(80.0, 80.0, 0.5);

        $this->assertSame(40.0, $points['project']);
        $this->assertSame(40.0, $points['objectives']);
    }

    public function test_the_split_is_adjustable(): void
    {
        $project = ProjectDeliveryScoreService::percentage(30, 0, 30);   // 100%
        $objectives = 50.0;

        // 70 project / 30 objectives: 100% of 70 + 50% of 30 = 85
        $this->assertSame(85.0, StaffKpiScoreService::blend($objectives, $project, 0.7));
    }
}
