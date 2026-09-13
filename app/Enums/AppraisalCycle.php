<?php

namespace App\Enums;

use Carbon\CarbonInterface;

/**
 * How often a member is appraised - staff.appraisal_cycle.
 *
 * Manual means no schedule: the administrator opens an appraisal whenever they
 * choose and nothing is ever "due". Every other value puts the member on a
 * rolling cycle, and AppraisalScheduleService works out when the next one is
 * due from their last generated appraisal.
 *
 * Stored as a readable string ("3m") for the same reason as AssessmentStatus:
 * someone reading the staff table should not need a lookup to know what 5 means.
 */
enum AppraisalCycle: string
{
    case Manual = 'manual';
    case OneWeek = '1w';
    case OneMonth = '1m';
    case TwoMonths = '2m';
    case ThreeMonths = '3m';
    case FourMonths = '4m';
    case SixMonths = '6m';
    case OneYear = '1y';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::OneWeek => '1 week',
            self::OneMonth => '1 month',
            self::TwoMonths => '2 months',
            self::ThreeMonths => '3 months',
            self::FourMonths => '4 months',
            self::SixMonths => '6 months',
            self::OneYear => '1 year',
        };
    }

    public function isScheduled(): bool
    {
        return $this !== self::Manual;
    }

    /**
     * The date one cycle after $date. Month lengths never overflow, so a cycle
     * from 31 January lands on 28/29 February, not in March.
     */
    public function after(CarbonInterface $date): ?CarbonInterface
    {
        $date = $date->copy();

        return match ($this) {
            self::Manual => null,
            self::OneWeek => $date->addWeek(),
            self::OneMonth => $date->addMonthsNoOverflow(1),
            self::TwoMonths => $date->addMonthsNoOverflow(2),
            self::ThreeMonths => $date->addMonthsNoOverflow(3),
            self::FourMonths => $date->addMonthsNoOverflow(4),
            self::SixMonths => $date->addMonthsNoOverflow(6),
            self::OneYear => $date->addYearNoOverflow(),
        };
    }
}
