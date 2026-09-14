<?php

namespace App\Ai\Tools;

use App\Enums\TaskStatus;
use App\Models\ProjectTask;
use App\Models\Staff;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

/**
 * A member's project tasks - what they have, what is done, what is late.
 */
class MemberTasks extends AssistantTool
{
    public function description(): string
    {
        return "List a member's project tasks with project, tag, points, due date and status. "
            .'Filter by status: open, done, overdue or all. Leave member empty for the person asking.';
    }

    public function handle(Request $request): string
    {
        $member = $this->resolveMember($request['member'] ?? null);

        if (! $member instanceof Staff) {
            return $member;
        }

        $status = $request['status'] ?? 'open';

        $query = ProjectTask::query()->with(['project', 'tag'])
            ->where('assignee_id', $member->id)
            ->when($status === 'open', fn ($q) => $q->where('status', '!=', TaskStatus::Done))
            ->when($status === 'done', fn ($q) => $q->where('status', TaskStatus::Done))
            ->when($status === 'overdue', fn ($q) => $q->where('status', '!=', TaskStatus::Done)->whereDate('due_date', '<', today()))
            ->orderByRaw('due_date IS NULL')->orderBy('due_date');

        $total = (clone $query)->count();

        return $this->json([
            'member' => $member->staff_name,
            'filter' => $status,
            'total' => $total,
            'shown' => min($total, $this->limit()),
            'tasks' => $query->limit($this->limit())->get()->map(fn (ProjectTask $t) => [
                'task' => $t->title,
                'project' => $t->project->title ?? null,
                'tag' => $t->tag->name ?? null,
                'points' => $this->num($t->points()),
                'status' => $t->status->label(),
                'due' => $t->due_date?->format('Y-m-d'),
                'overdue' => $t->isOverdue(),
                'completed' => $t->completed_at?->format('Y-m-d'),
            ])->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'member' => $schema->string()->description('Member name, or empty for the person asking.'),
            'status' => $schema->string()->enum(['open', 'done', 'overdue', 'all'])->description('Default open.'),
        ];
    }
}
