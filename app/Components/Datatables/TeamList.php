<?php

namespace App\Components\Datatables;

use App\Queries\TeamListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class TeamList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'team_name' => 'Team Name',
            'staff_count' => 'Active Members',
            'team_status' => 'Status',
            'action' => 'Actions',
        ];
    }

    public function centeredColumns(): array
    {
        return ['staff_count', 'team_status'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(app(TeamListQuery::class)->build(), $request);
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function ($team) {
            return [
                'team_name' => e($team->team_name),
                'staff_count' => $team->staff_count,
                'team_status' => $this->tbStatusToggle(route('teams.toggle-status', $team->team_id), $team->team_status),
                'action' => $this->actionButtons($team),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    private function actionButtons($team): string
    {
        $membersLink = \Illuminate\Support\Facades\Route::has('staff.index')
            ? $this->tbLink(route('staff.index', ['teamid' => $team->team_id]), 'ri-briefcase-line', 'tb-ac-btn-7', 'Members')
            : '';

        $editButton = $this->tbButton('ri-edit-2-line', 'tb-ac-btn-1', 'Edit', [
            'id' => $team->team_id,
            'name' => $team->team_name,
        ], 'js-edit-team');

        return $membersLink.' '.$editButton.' '.$this->tbDeleteForm(route('teams.destroy', $team->team_id));
    }
}
