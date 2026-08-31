<?php

namespace App\Components\Datatables;

use App\Enums\ObjectiveType;
use App\Queries\KpiObjectiveListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class KpiObjectiveList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'kojbInfo_title' => 'Title',
            'obj_type' => 'Type',
            'allowed_marks' => 'Allowed Marks',
            'action' => 'Actions',
        ];
    }

    public function centeredColumns(): array
    {
        return ['obj_type', 'allowed_marks'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(
            app(KpiObjectiveListQuery::class)->forRequest($request),
            $request,
            ['kojbInfo_title' => null, 'allowed_marks' => null]
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function ($objective) {
            $marks = $objective->mark?->allowedMarks() ?? [];

            return [
                'kojbInfo_title' => e($objective->info->kojbInfo_title ?? '-'),
                'obj_type' => $objective->obj_type->label(),
                'allowed_marks' => $marks ? implode(', ', array_map(fn ($m) => ($m > 0 ? '+' : '').$m, $marks)) : '-',
                'action' => $this->actionButtons($objective),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    private function actionButtons($objective): string
    {
        $mark = $objective->mark;

        $editButton = $this->tbButton('ri-edit-2-line', 'tb-ac-btn-1', 'Edit', [
            'id' => $objective->obj_id,
            'type' => $objective->obj_type->value,
            'mk2' => $mark?->objmk_2 ? 1 : 0,
            'mk1' => $mark?->objmk_1 ? 1 : 0,
            'mk0' => $mark?->objmk_0 ? 1 : 0,
            'mkn1' => $mark?->objmk_n1 ? 1 : 0,
            'mkn2' => $mark?->objmk_n2 ? 1 : 0,
        ], 'js-edit-objective');

        return $editButton.' '.$this->tbDeleteForm(route('kpi.objectives.destroy', [$objective->kpi_ID, $objective->obj_id]));
    }
}
