<?php

namespace App\Ai\Tools;

use App\Models\Staff;
use App\Services\StaffKpiScoreService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

/**
 * One member's KPI score for a period - the same figures as their KPI page.
 */
class MemberKpiScore extends AssistantTool
{
    public function description(): string
    {
        return "Get a member's KPI score out of 100 for a period, split into project points and KPI objective points, "
            .'with tasks done, points earned and the appraisal marks behind it. Leave member empty for the person asking.';
    }

    public function handle(Request $request): string
    {
        $member = $this->resolveMember($request['member'] ?? null);

        if (! $member instanceof Staff) {
            return $member;
        }

        [$from, $to, $label] = $this->period($request['period'] ?? null, $request['from'] ?? null, $request['to'] ?? null);
        $member->loadMissing(['position', 'team']);
        $score = app(StaffKpiScoreService::class)->finalScore($member, $from, $to);
        $d = $score['delivery_detail'];
        $o = $score['objective_detail'];

        return $this->json([
            'member' => $member->staff_name,
            'position' => $member->position->position_name ?? null,
            'team' => $member->team->team_name ?? null,
            'period' => $label,
            'kpi_score_out_of_100' => $this->num($score['percentage']),
            'project_points_earned' => $this->num($score['project_points']),
            'project_points_available' => $this->num($score['project_share']),
            'project_target_achieved_percent' => $this->num($score['delivery']),
            'objective_points_earned' => $this->num($score['objective_points']),
            'objective_points_available' => $this->num($score['objective_share']),
            'objective_marks_achieved_percent' => $this->num($score['objective']),
            'tasks_done' => $d['done'],
            'tasks_total' => $d['total'],
            'task_marks_earned' => $this->num($d['earned']),
            'task_marks_target' => $this->num($d['target']),
            'appraisals_counted' => $o['appraisals'],
            'note' => $score['objective'] === null
                ? 'No generated appraisal covers this period, so KPI objectives are not scored yet.'
                : null,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'member' => $schema->string()->description('Member name, or empty for the person asking.'),
            'period' => $schema->string()->enum(self::PERIODS)->description('Default this_year.'),
            'from' => $schema->string()->description('Start date YYYY-MM-DD, only with period=custom.'),
            'to' => $schema->string()->description('End date YYYY-MM-DD, only with period=custom.'),
        ];
    }
}
