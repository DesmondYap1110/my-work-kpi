<?php

namespace App\Components\Datatables;

use App\Components\Filters\SelectFilter;
use App\Enums\PhaseApprovalStatus;
use App\Enums\PhaseProgressStatus;
use App\Models\Project;
use App\Models\Team;
use App\Queries\ProjectPhaseListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ProjectPhaseList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'project_title' => 'Project',
            'p_PTitle' => 'Title',
            'p_Type' => 'Project Type',
            'p_DDate' => 'Due',
            'p_Status' => 'Approval',
            'p_ppstatus' => 'Progress',
            'action' => 'Actions',
        ];
    }

    public function filters(): array
    {
        $progress = collect(PhaseProgressStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();

        return [
            new SelectFilter('project_id', 'Project', Project::orderBy('p_Title')->pluck('p_Title', 'project_id')->all()),
            new SelectFilter('team_id', 'Team', Team::orderBy('team_name')->pluck('team_name', 'team_id')->all()),
            new SelectFilter('progress_status', 'Progress', $progress),
        ];
    }

    public function centeredColumns(): array
    {
        return ['p_Type', 'p_DDate', 'p_Status', 'p_ppstatus'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(
            app(ProjectPhaseListQuery::class)->forRequest($request),
            $request,
            ['project_title' => null]
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function ($phase) {
            return [
                'project_title' => e($phase->project->p_Title ?? '-'),
                'p_PTitle' => e($phase->p_PTitle),
                'p_Type' => $phase->p_Type->label(),
                'p_DDate' => $phase->p_DDate->format('d M Y'),
                'p_Status' => $this->approvalBadge($phase->p_Status),
                'p_ppstatus' => $this->progressBadge($phase->p_ppstatus),
                'action' => $this->actionButtons($phase),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    /**
     * Legacy shows a plain dash rather than a pill when nothing has been
     * submitted yet; the other three states keep its colour mapping.
     */
    private function approvalBadge(PhaseApprovalStatus $status): string
    {
        if ($status === PhaseApprovalStatus::NoSubmission) {
            return '-';
        }

        return $this->tbStatus($status->label(), match ($status) {
            PhaseApprovalStatus::Pending => 4,
            PhaseApprovalStatus::Approved => 1,
            PhaseApprovalStatus::Rejected => 5,
        });
    }

    private function progressBadge(?PhaseProgressStatus $status): string
    {
        if ($status === null) {
            return '-';
        }

        return $this->tbStatus($status->label(), match ($status) {
            PhaseProgressStatus::Progress => 4,
            PhaseProgressStatus::OnHold => 3,
            PhaseProgressStatus::Complete => 1,
        });
    }

    private function actionButtons($phase): string
    {
        $buttons = [];

        $buttons[] = $this->tbButton('ri-attachment-2', 'tb-ac-btn-7', 'Attachments', ['id' => $phase->p_PID], 'js-show-attachments');

        if ($phase->p_Status !== PhaseApprovalStatus::Approved) {
            $buttons[] = $this->tbForm(route('project-phases.approve', $phase->p_PID), 'POST', 'ri-check-line', 'tb-ac-btn-6', 'Approve');
        }

        if ($phase->p_Status !== PhaseApprovalStatus::Rejected) {
            $buttons[] = $this->tbForm(route('project-phases.reject', $phase->p_PID), 'POST', 'ri-close-line', 'tb-ac-btn-2', 'Reject');
        }

        $buttons[] = $this->tbLink(route('project-phases.edit', $phase->p_PID), 'ri-edit-2-line', 'tb-ac-btn-1', 'Edit');
        $buttons[] = $this->tbDeleteForm(route('project-phases.destroy', $phase->p_PID));

        return implode(' ', $buttons);
    }
}
