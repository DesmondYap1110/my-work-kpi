<?php

namespace App\Ai\Tools;

use App\Models\Assessment;
use App\Models\Staff;
use App\Services\AppraisalScheduleService;
use App\Services\AssessmentScoreService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

/**
 * A member's appraisals and when the next one is due. Members see only their
 * own generated appraisals - a draft is still the appraiser's.
 */
class MemberAppraisals extends AssistantTool
{
    public function description(): string
    {
        return "List a member's appraisals (review period, status, KPI score, band, appraiser) and when their next appraisal is due. "
            .'Administrator: set due_only=true to list everyone overdue or due soon. Leave member empty for the person asking.';
    }

    public function handle(Request $request): string
    {
        if (($request['due_only'] ?? false) && $this->isAdmin()) {
            return $this->json([
                'appraisals_due' => app(AppraisalScheduleService::class)->attention()->take($this->limit())->map(fn ($r) => [
                    'name' => $r['staff']->staff_name,
                    'due' => $r['due']?->format('Y-m-d'),
                    'state' => $r['state'],
                    'days' => $r['days'],
                    'cycle' => $r['cycle']->label(),
                ])->all(),
            ]);
        }

        $member = $this->resolveMember($request['member'] ?? null);

        if (! $member instanceof Staff) {
            return $member;
        }

        $scores = app(AssessmentScoreService::class);
        $appraisals = Assessment::query()->forStaff($member->id)
            ->with(['reviewer', 'position', 'template.bands', 'scores', 'staff'])
            ->when(! $this->isAdmin(), fn ($q) => $q->generated())
            ->orderByDesc('period_to')
            ->limit($this->limit())
            ->get();

        $schedule = app(AppraisalScheduleService::class)->rows(fn ($q) => $q->whereKey($member->id))->first();

        return $this->json([
            'member' => $member->staff_name,
            // Only active members are on the schedule.
            'review_cycle' => $schedule ? $schedule['cycle']->label() : null,
            'next_due' => $schedule && $schedule['due'] ? $schedule['due']->format('Y-m-d') : null,
            'next_due_state' => $schedule['state'] ?? null,
            'appraisals' => $appraisals->map(function (Assessment $a) use ($scores) {
                $s = $scores->summary($a);

                return [
                    'period' => $a->periodLabel(),
                    'status' => $a->status->label(),
                    'kpi_score' => $this->num($s['percentage']),
                    'band' => $s['band']->label ?? null,
                    'outcome' => $s['band']->outcome ?? null,
                    'appraiser' => $a->reviewer->staff_name ?? null,
                ];
            })->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'member' => $schema->string()->description('Member name, or empty for the person asking.'),
            'due_only' => $schema->boolean()->description('Administrator only: list members whose appraisal is overdue or due soon.'),
        ];
    }
}
