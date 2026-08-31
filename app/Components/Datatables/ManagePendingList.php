<?php

namespace App\Components\Datatables;

use App\Queries\ManagePendingListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ManagePendingList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'staff_name' => 'Name',
            'project_title' => 'Project',
            'objective_title' => 'Objective',
            'mark' => 'Mark',
            'createddate' => 'Date',
            'action' => 'Actions',
        ];
    }

    public function centeredColumns(): array
    {
        return ['mark', 'createddate'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(
            app(ManagePendingListQuery::class)->build(),
            $request,
            ['staff_name' => null, 'project_title' => null, 'objective_title' => null]
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function ($entry) {
            return [
                'staff_name' => e($entry->staff->staff_name ?? '-'),
                'project_title' => e($entry->project->p_Title ?? '-'),
                'objective_title' => e($entry->objectiveInfo->kojbInfo_title ?? '-'),
                'mark' => $entry->mark,
                'createddate' => $entry->createddate?->format('d M Y H:i'),
                'action' => $this->actionButtons($entry),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    private function actionButtons($entry): string
    {
        $approve = $this->tbForm(route('manage-pending.approve', $entry->kpiproject_id), 'POST', 'ri-check-line', 'tb-ac-btn-6', 'Approve');

        $reject = $this->tbButton('ri-close-line', 'tb-ac-btn-2', 'Reject', [
            'id' => $entry->kpiproject_id,
            'marks' => json_encode($entry->allowedMarksExcludingCurrent()),
        ], 'js-reject-pending');

        return $approve.' '.$reject;
    }
}
