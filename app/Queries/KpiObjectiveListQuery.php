<?php

namespace App\Queries;

use App\Models\KpiObjective;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class KpiObjectiveListQuery
{
    public function forRequest(Request $request): Builder
    {
        return KpiObjective::query()
            ->with(['info', 'mark'])
            ->where('kpi_ID', $request->integer('kid'))
            ->latest('obj_id');
    }
}
