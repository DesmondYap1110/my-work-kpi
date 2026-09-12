<?php

namespace App\Components\Datatables;

use App\Models\StaffPosition;
use App\Queries\KpiListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class KpiList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'position_name' => 'Position',
            'objectives_count' => 'Objectives',
            'action' => 'Actions',
        ];
    }

    /**
     * A KPI is created by picking a position, so the inline row is a single
     * dropdown. There is no 'update' route: nothing on an existing KPI is
     * editable - its objectives are managed on their own page.
     *
     * The options list shrinks as positions are used up, so 'reload' asks
     * the editor to reload the page after saving rather than just redrawing
     * the table, which would leave stale options in the dropdown.
     */
    public function inlineFields(): array
    {
        return [
            // Keyed by the COLUMN it occupies; 'name' is what it posts as.
            'position_name' => [
                'type' => 'select',
                'name' => 'position_id',
                'required' => true,
                'placeholder' => 'Select Position',
                'options' => StaffPosition::withoutKpi()
                    ->orderBy('position_name')
                    ->pluck('position_name', 'id')
                    ->all(),
            ],
        ];
    }

    public function inlineRoutes(): array
    {
        return [
            'store' => route('kpi.store'),
            'reload' => true,
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
        $rows = $result->getCollection()->map(function ($position) {
            return [
                'position_name' => $this->positionLink($position),
                'objectives_count' => $position->objectives_count,
                'action' => $this->actionButtons($position),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    /**
     * The position name doubles as the link to this KPI's objectives, so the
     * row doesn't spend an action button on it.
     */
    private function positionLink($position): string
    {
        return $this->tbTextLink(
            route('kpi.objectives.index', $position->id),
            $position->position_name,
            'Manage objectives'
        );
    }

    /**
     * Delete only. A KPI is a position plus its objectives, so there is
     * nothing on the record itself to edit - objectives are managed on their
     * own page, reached by the position link.
     */
    private function actionButtons($position): string
    {
        return $this->tbDeleteForm(route('kpi.destroy', $position->id));
    }
}
