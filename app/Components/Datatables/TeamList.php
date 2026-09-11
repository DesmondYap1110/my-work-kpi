<?php

namespace App\Components\Datatables;

use App\Queries\TeamListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

    public function inlineFields(): array
    {
        return [
            'team_name' => ['type' => 'text', 'required' => true, 'placeholder' => 'Team name'],
        ];
    }

    public function inlineRoutes(): array
    {
        return [
            'store' => route('teams.store'),
            'update' => route('teams.update', '__id__'),
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
                'team_name' => $this->nameLink($team),
                'staff_count' => $team->staff_count,
                'team_status' => $this->tbStatusToggle(route('teams.toggle-status', $team->team_id), $team->team_status),
                'action' => $this->actionButtons($team),
                '_inline' => ['team_name' => $team->team_name],
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    /**
     * The team name doubles as the link to its members, so the row doesn't
     * spend an action button on it.
     */
    private function nameLink($team): string
    {
        if (! Route::has('staff.index')) {
            return e($team->team_name);
        }

        return $this->tbTextLink(
            route('staff.index', ['teamid' => $team->team_id]),
            $team->team_name,
            'View members'
        );
    }

    private function actionButtons($team): string
    {
        $editButton = $this->tbButton('ri-edit-2-line', 'tb-ac-btn-1', 'Edit', [
            'id' => $team->team_id,
            'name' => $team->team_name,
        ], 'js-inline-editable');

        return $editButton.' '.$this->tbDeleteForm(route('teams.destroy', $team->team_id));
    }
}
