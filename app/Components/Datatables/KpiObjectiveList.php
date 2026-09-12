<?php

namespace App\Components\Datatables;

use App\Queries\KpiObjectiveListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Objectives for one position. Each row is a heading; its scored items are
 * managed on their own page, reached by the title link.
 */
class KpiObjectiveList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'title' => 'Objective',
            'category' => 'Category',
            'items_count' => 'Items',
            'action' => 'Actions',
        ];
    }

    public function centeredColumns(): array
    {
        return ['category', 'items_count'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(
            app(KpiObjectiveListQuery::class)->forRequest($request),
            $request,
            ['title' => null, 'category' => null, 'items_count' => null]
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function ($objective) {
            return [
                'title' => $this->titleLink($objective),
                'category' => e($objective->category->name ?? '-'),
                'items_count' => $objective->infos_count,
                'action' => $this->actionButtons($objective),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    /**
     * The objective title doubles as the link to its scored items.
     */
    private function titleLink($objective): string
    {
        return $this->tbTextLink(
            route('kpi.objectives.items.index', [$objective->position_id, $objective->id]),
            $objective->title ?: '(untitled)',
            'Manage items'
        );
    }

    private function actionButtons($objective): string
    {
        $editButton = $this->tbButton('ri-edit-2-line', 'tb-ac-btn-1', 'Edit', [
            'id' => $objective->id,
            'title' => $objective->title,
            'description' => $objective->description,
            'category' => $objective->category_id,
        ], 'js-edit-objective');

        return $editButton.' '.$this->tbDeleteForm(route('kpi.objectives.destroy', [$objective->position_id, $objective->id]));
    }
}
