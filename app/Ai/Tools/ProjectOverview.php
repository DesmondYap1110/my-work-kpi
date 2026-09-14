<?php

namespace App\Ai\Tools;

use App\Enums\TaskStatus;
use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

/**
 * Projects and how far along they are. Members see the projects they have
 * tasks on.
 */
class ProjectOverview extends AssistantTool
{
    public function description(): string
    {
        return 'List projects with status, dates, tasks done out of total and completion percent. '
            .'Optionally search by project name or filter by status.';
    }

    public function handle(Request $request): string
    {
        $query = Project::query()
            ->withCount(['tasks', 'tasks as done_count' => fn ($q) => $q->where('status', TaskStatus::Done)])
            ->when(! $this->isAdmin(), fn ($q) => $q->whereHas('tasks', fn ($t) => $t->where('assignee_id', $this->user->id)))
            ->when($request['name'] ?? null, fn ($q, $v) => $q->where('title', 'like', '%'.$v.'%'))
            ->orderByDesc('start_date');

        $projects = $query->limit($this->limit())->get()
            // "In Progress", "in-progress" and "InProgress" all match.
            ->filter(fn (Project $p) => ! ($request['status'] ?? null)
                || strcasecmp(preg_replace('/[^a-z]/i', '', $p->status->label()), preg_replace('/[^a-z]/i', '', $request['status'])) === 0)
            ->values();

        return $this->json([
            'projects' => $projects->map(fn (Project $p) => [
                'project' => $p->title,
                'status' => $p->status->label(),
                'start' => $p->start_date?->format('Y-m-d'),
                'end' => $p->end_date?->format('Y-m-d'),
                'tasks_done' => $p->done_count,
                'tasks_total' => $p->tasks_count,
                'completion_percent' => $p->tasks_count ? round($p->done_count / $p->tasks_count * 100) : null,
            ])->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Part of a project name. Optional.'),
            'status' => $schema->string()->enum(['Active', 'In-Progress', 'Completed', 'Cancelled'])->description('Optional.'),
        ];
    }
}
