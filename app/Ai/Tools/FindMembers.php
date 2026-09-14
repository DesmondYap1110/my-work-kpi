<?php

namespace App\Ai\Tools;

use App\Models\Staff;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

/**
 * Who works here: search members by name, team or position.
 */
class FindMembers extends AssistantTool
{
    public function description(): string
    {
        return 'Search members (staff) by name, team or position. Returns name, position, team, active status and join date. '
            .'Use it to find who is in a team or to check a name before other lookups.';
    }

    public function handle(Request $request): string
    {
        if (! $this->isAdmin()) {
            $me = $this->user->loadMissing(['position', 'team']);

            return $this->json(['note' => 'Members can only see themselves.', 'members' => [$this->row($me)]]);
        }

        $query = Staff::query()->excludingAdmin()->with(['position', 'team'])
            ->when($request['name'] ?? null, fn ($q, $v) => $q->where('staff_name', 'like', '%'.$v.'%'))
            ->when($request['team'] ?? null, fn ($q, $v) => $q->whereHas('team', fn ($t) => $t->where('team_name', 'like', '%'.$v.'%')))
            ->when($request['position'] ?? null, fn ($q, $v) => $q->whereHas('position', fn ($p) => $p->where('position_name', 'like', '%'.$v.'%')))
            ->when(! ($request['include_inactive'] ?? false), fn ($q) => $q->active())
            ->orderBy('staff_name');

        $total = (clone $query)->count();

        return $this->json([
            'total_matching' => $total,
            'shown' => min($total, $this->limit()),
            'members' => $query->limit($this->limit())->get()->map(fn (Staff $s) => $this->row($s))->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Part of a member name. Optional.'),
            'team' => $schema->string()->description('Team name, e.g. "Team A". Optional.'),
            'position' => $schema->string()->description('Position name, e.g. "Back End Developer". Optional.'),
            'include_inactive' => $schema->boolean()->description('Also include deactivated members. Default false.'),
        ];
    }

    private function row(Staff $s): array
    {
        return [
            'name' => $s->staff_name,
            'position' => $s->position->position_name ?? null,
            'team' => $s->team->team_name ?? null,
            'active' => (bool) $s->is_active,
            'joined' => $s->company_joined_date?->format('Y-m-d'),
        ];
    }
}
