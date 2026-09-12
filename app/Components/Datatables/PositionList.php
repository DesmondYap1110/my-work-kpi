<?php

namespace App\Components\Datatables;

use App\Queries\PositionListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PositionList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'position_name' => 'Position Name',
            'job_scope' => 'Job Scope',
            'has_kpi' => 'KPI Assigned',
            'action' => 'Actions',
        ];
    }

    public function inlineFields(): array
    {
        return [
            'position_name' => ['type' => 'text', 'required' => true, 'placeholder' => 'Position name'],
            'job_scope' => ['type' => 'textarea', 'placeholder' => 'Job scope'],
        ];
    }

    public function inlineRoutes(): array
    {
        return [
            'store' => route('positions.store'),
            'update' => route('positions.update', '__id__'),
        ];
    }

    public function centeredColumns(): array
    {
        return ['has_kpi'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(app(PositionListQuery::class)->build(), $request);
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function ($position) {
            return [
                'position_name' => $this->nameLink($position),
                'job_scope' => e(\Illuminate\Support\Str::limit($position->job_scope, 80)),
                'has_kpi' => $this->kpiStatusAction($position),
                'action' => $this->actionButtons($position),
                '_inline' => [
                    'position_name' => $position->position_name,
                    'job_scope' => $position->job_scope,
                ],
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    /**
     * The position name doubles as the link to its members, so the row
     * doesn't spend an action button on it.
     */
    private function nameLink($position): string
    {
        if (! Route::has('staff.index')) {
            return e($position->position_name);
        }

        return $this->tbTextLink(
            route('staff.index', ['pid' => $position->id]),
            $position->position_name,
            'View members'
        );
    }

    /**
     * The KPI Assigned cell is the way in to a position's objectives:
     *
     *   assigned     -> link straight to the objectives page
     *   not assigned -> one click assigns the KPI and lands on that same page
     *
     * Posting rather than linking for the unassigned case, because it creates
     * a record. KpiController::store redirects on to the objectives page.
     */
    private function kpiStatusAction($position): string
    {
        // Administrator is the portal's access gate, not a reviewed job -
        // there is nothing to assign, so the cell offers no action.
        if ($position->isAdministrator()) {
            return '<span class="kpi-empty">n/a</span>';
        }

        if ($position->has_kpi) {
            return '<a href="'.route('kpi.objectives.index', $position->id).'"'
                .' class="tb-status" id="tb-status-1" title="Manage objectives">Yes</a>';
        }

        return '<form action="'.route('kpi.store').'" method="POST" class="d-inline">'
            .csrf_field()
            .'<input type="hidden" name="position_id" value="'.$position->id.'">'
            .'<button type="submit" class="tb-status" id="tb-status-2" style="border:none;"'
            .' title="Assign a KPI and add objectives">No</button>'
            .'</form>';
    }

    private function actionButtons($position): string
    {
        $editButton = $this->tbButton('ri-edit-2-line', 'tb-ac-btn-1', 'Edit', [
            'id' => $position->id,
            'name' => $position->position_name,
            'scope' => $position->job_scope,
        ], 'js-inline-editable');

        return $editButton.' '.$this->tbDeleteForm(route('positions.destroy', $position->id));
    }
}
