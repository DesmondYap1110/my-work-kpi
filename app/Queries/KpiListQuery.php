<?php

namespace App\Queries;

use App\Models\StaffPosition;
use Illuminate\Database\Eloquent\Builder;

class KpiListQuery
{
    /**
     * "KPIs" are positions that have one assigned - there is no separate KPI
     * record, so this lists positions that have objectives, along with how
     * many objectives each carries.
     */
    public function build(): Builder
    {
        return StaffPosition::query()
            ->has('objectives')
            ->withCount('objectives')
            ->latest('id');
    }
}
