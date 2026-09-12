<?php

namespace App\Components\Datatables;

use App\Queries\KpiObjectiveItemListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * The scored items under one objective: what each is, the marks it allows,
 * and whether it counts as Standard or Extra.
 */
class KpiObjectiveItemList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'title' => 'Item',
            'description' => 'Description',
            'objective_type' => 'Type',
            'allowed_marks' => 'Allowed Marks',
            'action' => 'Actions',
        ];
    }

    public function centeredColumns(): array
    {
        return ['objective_type', 'allowed_marks'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(
            app(KpiObjectiveItemListQuery::class)->forRequest($request),
            $request,
            ['title' => null, 'objective_type' => null]
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function ($item) {
            $marks = $item->allowedMarks();

            return [
                'title' => e($item->title),
                'description' => e(\Illuminate\Support\Str::limit($item->description, 60)) ?: '-',
                'objective_type' => $item->objective_type->label(),
                'allowed_marks' => $marks
                    ? implode(', ', array_map(fn ($m) => ($m > 0 ? '+' : '').$m, $marks))
                    : '-',
                'action' => $this->actionButtons($item),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    private function actionButtons($item): string
    {
        $editButton = $this->tbButton('ri-edit-2-line', 'tb-ac-btn-1', 'Edit', [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'type' => $item->objective_type->value,
            // The allowed marks travel as a JSON array, which jQuery's .data()
            // parses back into a real array on the other side.
            'marks' => json_encode($item->allowedMarks()),
        ], 'js-edit-item');

        return $editButton.' '.$this->tbDeleteForm(
            route('kpi.objectives.items.destroy', [$item->objective->position_id, $item->objective_id, $item->id])
        );
    }
}
