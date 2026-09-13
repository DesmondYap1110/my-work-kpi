<?php

namespace App\Services;

use App\Enums\AppraisalCycle;
use App\Models\Assessment;
use App\Models\KpiSetting;
use App\Models\Staff;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * When each member's next appraisal is due, from their review cycle.
 *
 *   last appraisal set a "next assessment" date  -> that date
 *   otherwise, last generated appraisal           -> its period end + cycle
 *   never appraised                               -> joined date + cycle
 *                                                   (today, with no joined date)
 *
 * The appraiser's own "next assessment" date wins because it is a decision
 * someone made about this person; the cycle is only the default.
 *
 * Manual members have no due date and never appear as due.
 */
class AppraisalScheduleService
{
    /** Used only until the setting has been saved - see noticeDays(). */
    public const DEFAULT_NOTICE_DAYS = 3;

    private ?int $noticeDays = null;

    /**
     * Due within this many days counts as "due soon" and is notified - the
     * company's own lead time, set on the Review Schedule page.
     */
    public function noticeDays(): int
    {
        return $this->noticeDays ??= (int) (KpiSetting::current()->appraisal_notice_days ?? self::DEFAULT_NOTICE_DAYS);
    }

    /**
     * One row per active member (administrator excluded), soonest due first,
     * with Manual members last.
     *
     * @return Collection<int, array{staff: Staff, cycle: AppraisalCycle, last: Assessment|null, due: CarbonInterface|null, state: string, days: int|null}>
     */
    public function rows(?\Closure $scope = null): Collection
    {
        // $scope narrows the members first (a team, a name search), so a
        // filtered page only works out the rows it will show.
        $members = Staff::active()->excludingAdmin()
            ->with(['position', 'team'])
            ->when($scope, $scope)
            ->orderBy('staff_name')
            ->get();

        // Only the columns a due date needs - these tables grow by one row
        // per member per review, forever.
        $lastByStaff = Assessment::query()
            ->generated()
            ->whereIn('staff_id', $members->pluck('id'))
            ->whereNotNull('period_to')
            ->orderByDesc('period_to')
            ->get(['id', 'staff_id', 'period_from', 'period_to', 'next_assessment_date'])
            ->unique('staff_id')
            ->keyBy('staff_id');

        // A draft already open, so the schedule offers to continue it rather
        // than start a second one.
        $draftByStaff = Assessment::query()
            ->where('status', \App\Enums\AssessmentStatus::Draft)
            ->whereIn('staff_id', $members->pluck('id'))
            ->latest('id')
            ->get(['id', 'staff_id', 'period_from', 'period_to'])
            ->unique('staff_id')
            ->keyBy('staff_id');

        return $members
            ->map(fn (Staff $staff) => $this->row($staff, $lastByStaff->get($staff->id)) + ['draft' => $draftByStaff->get($staff->id)])
            ->sortBy(fn ($row) => $row['due'] ? $row['due']->timestamp : PHP_INT_MAX)
            ->values();
    }

    /**
     * Only the members whose appraisal is overdue or due soon - for the
     * dashboard notice.
     */
    public function attention(): Collection
    {
        return $this->rows()->filter(fn ($row) => in_array($row['state'], ['overdue', 'soon'], true))->values();
    }

    /**
     * @return array{staff: Staff, cycle: AppraisalCycle, last: Assessment|null, due: CarbonInterface|null, state: string, days: int|null}
     */
    public function row(Staff $staff, ?Assessment $last): array
    {
        $cycle = AppraisalCycle::tryFrom((string) $staff->appraisal_cycle) ?? AppraisalCycle::Manual;
        $due = $this->dueDate($staff, $cycle, $last);
        $days = $due ? (int) today()->diffInDays($due, false) : null;

        return [
            'staff' => $staff,
            'cycle' => $cycle,
            'last' => $last,
            'due' => $due,
            'days' => $days,
            'state' => self::state($days, $this->noticeDays()),
        ];
    }

    public function dueDate(Staff $staff, AppraisalCycle $cycle, ?Assessment $last): ?CarbonInterface
    {
        if (! $cycle->isScheduled()) {
            return null;
        }

        if ($last?->next_assessment_date) {
            return $last->next_assessment_date->copy()->startOfDay();
        }

        $from = $last?->period_to ?? $staff->company_joined_date ?? null;

        return $from ? $cycle->after(Carbon::parse($from))->startOfDay() : today();
    }

    /**
     * manual / overdue / soon / scheduled, from days until due (negative is
     * past). Static so the rule can be tested on its own.
     */
    public static function state(?int $days, int $noticeDays = self::DEFAULT_NOTICE_DAYS): string
    {
        return match (true) {
            $days === null => 'manual',
            $days < 0 => 'overdue',
            $days <= $noticeDays => 'soon',
            default => 'scheduled',
        };
    }
}
