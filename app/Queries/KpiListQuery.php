<?php

namespace App\Queries;

use App\Models\StaffPosition;
use Illuminate\Database\Eloquent\Builder;

class KpiListQuery
{
    /**
     * "KPIs" are positions that have one assigned - there is no separate KPI
     * record, so this lists positions flagged with has_kpi along with how
     * many objectives each carries.
     */
    public function build(): Builder
    {
        return StaffPosition::query()
            ->where('has_kpi', true)
            ->withCount('objectives')
            ->latest('id');
    }
}
