<?php

namespace Tests\Feature;

use App\Enums\AppraisalCycle;
use App\Services\AppraisalScheduleService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * The review cycle: when the next appraisal falls, and when it needs the
 * administrator's attention.
 */
class AppraisalScheduleTest extends TestCase
{
    public function test_each_cycle_lands_on_the_right_date(): void
    {
        $from = Carbon::parse('2026-01-15');

        $this->assertSame('2026-01-22', AppraisalCycle::OneWeek->after($from)->toDateString());
        $this->assertSame('2026-02-15', AppraisalCycle::OneMonth->after($from)->toDateString());
        $this->assertSame('2026-03-15', AppraisalCycle::TwoMonths->after($from)->toDateString());
        $this->assertSame('2026-04-15', AppraisalCycle::ThreeMonths->after($from)->toDateString());
        $this->assertSame('2026-05-15', AppraisalCycle::FourMonths->after($from)->toDateString());
        $this->assertSame('2026-07-15', AppraisalCycle::SixMonths->after($from)->toDateString());
        $this->assertSame('2027-01-15', AppraisalCycle::OneYear->after($from)->toDateString());
    }

    public function test_a_month_from_the_31st_does_not_spill_into_the_next_month(): void
    {
        $this->assertSame('2026-02-28', AppraisalCycle::OneMonth->after(Carbon::parse('2026-01-31'))->toDateString());
    }

    public function test_manual_has_no_due_date(): void
    {
        $this->assertNull(AppraisalCycle::Manual->after(Carbon::parse('2026-01-15')));
        $this->assertFalse(AppraisalCycle::Manual->isScheduled());
    }

    public function test_state_from_days_until_due(): void
    {
        $this->assertSame('manual', AppraisalScheduleService::state(null));
        $this->assertSame('overdue', AppraisalScheduleService::state(-1));
        $this->assertSame('soon', AppraisalScheduleService::state(0));
        $this->assertSame('soon', AppraisalScheduleService::state(3, 3));
        $this->assertSame('scheduled', AppraisalScheduleService::state(4, 3));
    }

    /**
     * The notice period is the company's setting, not a fixed week.
     */
    public function test_the_notice_period_decides_what_is_due_soon(): void
    {
        $this->assertSame('scheduled', AppraisalScheduleService::state(5, 3));
        $this->assertSame('soon', AppraisalScheduleService::state(5, 7));
        $this->assertSame('soon', AppraisalScheduleService::state(0, 0));
        $this->assertSame('scheduled', AppraisalScheduleService::state(1, 0));
    }
}
