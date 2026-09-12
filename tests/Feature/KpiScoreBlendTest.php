<?php

namespace Tests\Feature;

use App\Models\KpiSetting;
use App\Services\StaffKpiScoreService;
use Tests\TestCase;

/**
 * The rule that decides what a KPI score actually is.
 *
 * Every case here is a decision rather than an implementation detail - above
 * all that a missing half is not a zero, which is what lets a company with no
 * projects use the app unchanged.
 */
class KpiScoreBlendTest extends TestCase
{
    public function test_an_even_split_averages_the_two_halves(): void
    {
        $this->assertSame(75.0, StaffKpiScoreService::blend(70.0, 80.0, 0.5));
    }

    public function test_a_seventy_thirty_split_favours_delivery(): void
    {
        // 60 x 0.7 + 90 x 0.3 = 42 + 27
        $this->assertSame(69.0, StaffKpiScoreService::blend(90.0, 60.0, 0.7));
    }

    public function test_no_delivery_leaves_the_objective_score_standing_alone(): void
    {
        // A company that runs no projects, or a member with nothing assigned:
        // the weight is irrelevant because there is no delivery to weigh.
        $this->assertSame(82.0, StaffKpiScoreService::blend(82.0, null, 0.7));
    }

    public function test_no_objectives_leaves_the_delivery_score_standing_alone(): void
    {
        $this->assertSame(64.0, StaffKpiScoreService::blend(null, 64.0, 0.3));
    }

    public function test_nothing_to_score_is_null_rather_than_zero(): void
    {
        $this->assertNull(StaffKpiScoreService::blend(null, null, 0.5));
    }

    public function test_a_zero_weight_ignores_delivery_entirely(): void
    {
        $this->assertSame(90.0, StaffKpiScoreService::blend(90.0, 10.0, 0.0));
    }

    public function test_a_full_weight_ignores_objectives_entirely(): void
    {
        $this->assertSame(10.0, StaffKpiScoreService::blend(90.0, 10.0, 1.0));
    }

    public function test_a_real_zero_still_counts_against_the_score(): void
    {
        // Delivered nothing of what was assigned - that is a genuine 0, and
        // must not be confused with having nothing assigned.
        $this->assertSame(27.0, StaffKpiScoreService::blend(90.0, 0.0, 0.7));
    }

    public function test_the_setting_converts_a_percentage_to_a_share(): void
    {
        $this->assertSame(0.7, (new KpiSetting(['project_weight' => 70]))->projectShare());
        $this->assertSame(0.0, (new KpiSetting(['project_weight' => 0]))->projectShare());
    }

    public function test_a_nonsense_weight_is_clamped_rather_than_trusted(): void
    {
        $this->assertSame(1.0, (new KpiSetting(['project_weight' => 250]))->projectShare());
    }
}
