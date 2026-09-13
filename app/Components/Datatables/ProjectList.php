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
            'title' => 'Title',
            'start_date' => 'Start',
            'end_date' => 'End',
            'status' => 'Status',
            'action' => 'Actions',
        ];
    }

    public function filters(): array
    {
        $statuses = collect(ProjectStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();

        return [
            new SelectFilter('project_id', 'Project', Project::orderBy('title')->pluck('title', 'id')->all()),
            new SelectFilter('status', 'Status', $statuses),
            new DateFilter('date_from', 'Range From'),
            new DateFilter('date_to', 'Range To'),
        ];
    }

    public function centeredColumns(): array
    {
        return ['start_date', 'end_date', 'status'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(
            app(ProjectListQuery::class)->forRequest($request),
            $request,
            []
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function ($project) {
            return [
                // The title doubles as the way in to the project's tasks, the
                // same way a position's name opens its members.
                'title' => $this->tbTextLink(route('projects.show', $project->id), $project->title, 'Open tasks'),
                'start_date' => $project->start_date->format('d M Y'),
                'end_date' => $project->end_date->format('d M Y'),
                'status' => $this->statusBadge($project->status),
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

    /**
     * Every member may start a project and plan it, so this list is not
     * administrator-only. Pricing the work is - see the tag field in
     * projects/_task-fields.blade.php.
     */
    public static function adminOnly(): bool
    {
        return false;
    }

    private function actionButtons($project): string
    {
        $status = $project->status;

        // No "open tasks" or "edit" button: the project title opens the
        // project, and Edit Project lives on that page - see
        // projects/show.blade.php.
        $buttons = [];

        // Cancelling and deleting end a project and take its tasks with it -
        // and with them the record a KPI was scored from. Members add and
        // edit; ending one is the administrator's.
        if (static::viewerIsAdmin()) {
            if (in_array($status, [ProjectStatus::Active, ProjectStatus::InProgress], true)) {
                // Opens a dialog asking for the reason rather than a yes/no
                // confirm - see projects/index.blade.php and project/modals.js.
                $buttons[] = $this->tbButton('ri-close-circle-line', 'tb-ac-btn-3', 'Cancel', [
                    'id' => $project->id,
                    'title' => $project->title,
                ], 'js-cancel-project');
            }

            if ($status === ProjectStatus::Active) {
                $buttons[] = $this->tbDeleteForm(route('projects.destroy', $project->id));
            }
        }

        // A cancelled or completed project has nothing left to act on.
        return $buttons === [] ? '-' : implode(' ', $buttons);
    }
}
