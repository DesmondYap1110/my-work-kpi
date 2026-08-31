<?php

namespace App\Components\Datatables;

use App\Components\Filters\DateFilter;
use App\Components\Filters\SelectFilter;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Queries\ProjectListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ProjectList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'p_Title' => 'Title',
            'team_name' => 'Team',
            'p_SDate' => 'Start',
            'p_EDate' => 'End',
            'p_status' => 'Status',
            'action' => 'Actions',
        ];
    }

    public function filters(): array
    {
        $statuses = collect(ProjectStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();

        return [
            new SelectFilter('project_id', 'Project', Project::orderBy('p_Title')->pluck('p_Title', 'project_id')->all()),
            new SelectFilter('status', 'Status', $statuses),
            new DateFilter('date_from', 'Range From'),
            new DateFilter('date_to', 'Range To'),
        ];
    }

    public function centeredColumns(): array
    {
        return ['p_SDate', 'p_EDate', 'p_status'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(
            app(ProjectListQuery::class)->forRequest($request),
            $request,
            ['team_name' => null]
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function ($project) {
            return [
                'p_Title' => e($project->p_Title),
                'team_name' => e($project->team->team_name ?? '-'),
                'p_SDate' => $project->p_SDate->format('d M Y'),
                'p_EDate' => $project->p_EDate->format('d M Y'),
                'p_status' => $this->statusBadge($project->p_status),
                'action' => $this->actionButtons($project),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    private function statusBadge(ProjectStatus $status): string
    {
        return $this->tbStatus($status->label(), match ($status) {
            ProjectStatus::Active => 5,
            ProjectStatus::Cancelled => 2,
            ProjectStatus::InProgress => 4,
            ProjectStatus::Completed => 1,
        });
    }

    private function actionButtons($project): string
    {
        $status = $project->p_status;
        $buttons = [];

        if (in_array($status, [ProjectStatus::Completed, ProjectStatus::InProgress, ProjectStatus::Cancelled], true)) {
            $buttons[] = $this->tbLink(route('project-phases.index', ['project_id' => $project->project_id]), 'ri-file-list-3-line', 'tb-ac-btn-7', 'Phases');
        }

        if (in_array($status, [ProjectStatus::Active, ProjectStatus::InProgress], true)) {
            $buttons[] = $this->tbLink(route('project-phases.create', ['project_id' => $project->project_id]), 'ri-add-line', 'tb-ac-btn-6', 'Add Phase');
            $buttons[] = $this->tbLink(route('projects.edit', $project->project_id), 'ri-edit-2-line', 'tb-ac-btn-1', 'Edit');
            $buttons[] = $this->tbForm(route('projects.cancel', $project->project_id), 'POST', 'ri-close-circle-line', 'tb-ac-btn-3', 'Cancel', 'js-confirm-cancel');
        }

        if ($status === ProjectStatus::Active) {
            $buttons[] = $this->tbDeleteForm(route('projects.destroy', $project->project_id));
        }

        return implode(' ', $buttons);
    }
}
