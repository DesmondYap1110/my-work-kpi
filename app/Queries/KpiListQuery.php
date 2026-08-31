<?php

namespace App\Queries;

use App\Models\Kpi;
use Illuminate\Database\Eloquent\Builder;

class KpiListQuery
{
    public function build(): Builder
    {
        return Kpi::query()
            ->with('position')
            ->withCount('objectives')
            ->latest('kpi_id');
    }
}
