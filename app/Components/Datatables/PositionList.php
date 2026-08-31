<?php

namespace App\Components\Datatables;

use App\Queries\PositionListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class PositionList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'position_name' => 'Position Name',
            'job_scope' => 'Job Scope',
            'kpistatus' => 'KPI Assigned',
            'action' => 'Actions',
        ];
    }

    public function centeredColumns(): array
    {
        return ['kpistatus'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(app(PositionListQuery::class)->build(), $request);
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function ($position) {
            return [
                'position_name' => e($position->position_name),
                'job_scope' => e(\Illuminate\Support\Str::limit($position->job_scope, 80)),
                'kpistatus' => $position->kpistatus
                    ? '<span class="tb-status" id="tb-status-1">Yes</span>'
                    : '<span class="tb-status" id="tb-status-2">No</span>',
                'action' => $this->actionButtons($position),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    private function actionButtons($position): string
    {
        $membersLink = \Illuminate\Support\Facades\Route::has('staff.index')
            ? $this->tbLink(route('staff.index', ['pid' => $position->position_ID]), 'ri-briefcase-line', 'tb-ac-btn-7', 'Members')
            : '';

        $editButton = $this->tbButton('ri-edit-2-line', 'tb-ac-btn-1', 'Edit', [
            'id' => $position->position_ID,
            'name' => $position->position_name,
            'scope' => $position->job_scope,
        ], 'js-edit-position');

        return $membersLink.' '.$editButton.' '.$this->tbDeleteForm(route('positions.destroy', $position->position_ID));
    }
}
