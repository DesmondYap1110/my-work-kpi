<?php

namespace App\Components\Datatables;

use App\Queries\KpiListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class KpiList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'position_name' => 'Position',
            'kpi_title' => 'Title',
            'objectives_count' => 'Objectives',
            'action' => 'Actions',
        ];
    }

    public function centeredColumns(): array
    {
        return ['objectives_count'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(
            app(KpiListQuery::class)->build(),
            $request,
            ['position_name' => null, 'objectives_count' => null]
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function ($kpi) {
            return [
                'position_name' => e($kpi->position->position_name ?? '-'),
                'kpi_title' => e($kpi->kpi_title),
                'objectives_count' => $kpi->objectives_count,
                'action' => $this->actionButtons($kpi),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    private function actionButtons($kpi): string
    {
        $objectivesLink = $this->tbLink(route('kpi.objectives.index', $kpi->kpi_id), 'ri-list-check-2', 'tb-ac-btn-7', 'Manage Objectives');

        $editButton = $this->tbButton('ri-edit-2-line', 'tb-ac-btn-1', 'Edit', [
            'id' => $kpi->kpi_id,
            'title' => $kpi->kpi_title,
        ], 'js-edit-kpi');

        return $objectivesLink.' '.$editButton.' '.$this->tbDeleteForm(route('kpi.destroy', $kpi->kpi_id));
    }
}
