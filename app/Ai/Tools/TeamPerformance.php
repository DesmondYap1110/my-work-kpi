<?php

namespace App\Ai\Tools;

use App\Models\AssessmentTemplate;
use App\Models\Staff;
use App\Services\KpiReportService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

/**
 * Company or team performance - the KPI Report's summary, team table and
 * member ranking. Administrator only.
 */
class TeamPerformance extends AssistantTool
{
    public function description(): string
    {
        return 'Company-wide or per-team KPI performance for a period: highest, lowest and average KPI score, '
            .'each team\'s average and top performer, and members ranked by KPI score. Administrator only.';
    }

    public function handle(Request $request): string
    {
        if (! $this->isAdmin()) {
            return 'Not allowed: team and company performance is for the administrator only.';
        }

        [$from, $to, $label] = $this->period($request['period'] ?? null, $request['from'] ?? null, $request['to'] ?? null);

        $members = Staff::active()->excludingAdmin()->with(['position', 'team'])
            ->when($request['team'] ?? null, fn ($q, $v) => $q->whereHas('team', fn ($t) => $t->where('team_name', 'like', '%'.$v.'%')))
            ->when($request['position'] ?? null, fn ($q, $v) => $q->whereHas('position', fn ($p) => $p->where('position_name', 'like', '%'.$v.'%')))
            ->get();

        if ($members->isEmpty()) {
            return 'No active members match that team or position.';
        }

        $reports = app(KpiReportService::class);
        $scores = $reports->scores($members, $from, $to);
        $summary = $reports->summary($scores);
        $bands = AssessmentTemplate::current()->load('bands');

        return $this->json([
            'period' => $label,
            'members' => $summary['members'],
            'members_scored' => $summary['scored'],
            'average_kpi' => $this->num($summary['percentage']),
            'highest' => $summary['highest'] ? ['name' => $summary['highest']['staff']->staff_name, 'kpi' => $this->num($summary['highest']['percentage'])] : null,
            'lowest' => $summary['lowest'] ? ['name' => $summary['lowest']['staff']->staff_name, 'kpi' => $this->num($summary['lowest']['percentage'])] : null,
            'teams' => $reports->teams($scores, $bands)->map(fn ($t) => [
                'team' => $t['team'],
                'average_kpi' => $this->num($t['percentage']),
                'members' => $t['members'],
                'top_performer' => $t['top']['staff']->staff_name ?? null,
                'status' => $t['band']->label ?? null,
            ])->all(),
            'ranking' => $scores->filter(fn ($s) => $s['percentage'] !== null)->sortByDesc('percentage')->take($this->limit())->values()
                ->map(fn ($s, $i) => [
                    'rank' => $i + 1,
                    'name' => $s['staff']->staff_name,
                    'team' => $s['staff']->team->team_name ?? null,
                    'kpi' => $this->num($s['percentage']),
                    'band' => $bands->bandFor($s['percentage'])->label ?? null,
                ])->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'period' => $schema->string()->enum(self::PERIODS)->description('Default this_year.'),
            'from' => $schema->string()->description('Start date YYYY-MM-DD, only with period=custom.'),
            'to' => $schema->string()->description('End date YYYY-MM-DD, only with period=custom.'),
            'team' => $schema->string()->description('Limit to one team. Optional.'),
            'position' => $schema->string()->description('Limit to one position. Optional.'),
        ];
    }
}
