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
            ->with('category')
            ->withCount('infos')
            ->where('position_id', $request->integer('kid'))
            ->latest('id');
    }
}
