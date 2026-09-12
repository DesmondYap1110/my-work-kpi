<?php

namespace App\Components\Datatables;

use App\Components\Filters\SelectFilter;
use App\Models\StaffPosition;
use App\Models\Team;
use App\Queries\StaffListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class StaffList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'staff_name' => 'Name',
            'email' => 'Email',
            'position_name' => 'Position',
            'team_name' => 'Team',
            'is_active' => 'Status',
            'action' => 'Actions',
        ];
    }

    public function filters(): array
    {
        return [
            new SelectFilter('pid', 'Position', StaffPosition::orderBy('position_name')->pluck('position_name', 'id')->all()),
            new SelectFilter('teamid', 'Team', Team::orderBy('team_name')->pluck('team_name', 'id')->all()),
        ];
    }

    public function centeredColumns(): array
    {
        return ['is_active'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(
            app(StaffListQuery::class)->forRequest($request),
            $request,
            ['position_name' => null, 'team_name' => null]
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function ($staff) {
            return [
                'staff_name' => e($staff->staff_name),
                'email' => e($staff->email),
                'position_name' => e($staff->position->position_name ?? '-'),
                'team_name' => e($staff->team->team_name ?? '-'),
                'is_active' => $this->tbStatusToggle(route('staff.toggle-status', $staff->id), $staff->is_active, 'Active', 'Blocked'),
                'action' => $this->actionButtons($staff),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    private function actionButtons($staff): string
    {
        $viewKpi = $this->tbLink(route('staff.view-kpi', $staff->id), 'ri-bar-chart-2-line', 'tb-ac-btn-7', 'View KPI');
        $edit = $this->tbLink(route('staff.edit', $staff->id), 'ri-edit-2-line', 'tb-ac-btn-1', 'Edit');

        return $viewKpi.' '.$edit.' '.$this->tbDeleteForm(route('staff.destroy', $staff->id));
    }
}
