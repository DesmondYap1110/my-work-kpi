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
            'kpistatus' => 'KPI Assigned',
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
                'position_name' => $this->nameLink($position),
                'job_scope' => e(\Illuminate\Support\Str::limit($position->job_scope, 80)),
                'kpistatus' => $position->kpistatus
                    ? '<span class="tb-status" id="tb-status-1">Yes</span>'
                    : '<span class="tb-status" id="tb-status-2">No</span>',
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
            route('staff.index', ['pid' => $position->position_ID]),
            $position->position_name,
            'View members'
        );
    }

    private function actionButtons($position): string
    {
        $editButton = $this->tbButton('ri-edit-2-line', 'tb-ac-btn-1', 'Edit', [
            'id' => $position->position_ID,
            'name' => $position->position_name,
            'scope' => $position->job_scope,
        ], 'js-inline-editable');

        return $editButton.' '.$this->tbDeleteForm(route('positions.destroy', $position->position_ID));
    }
}
