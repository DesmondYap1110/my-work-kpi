<?php

namespace App\Queries;

use App\Models\KpiObjectiveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class KpiObjectiveItemListQuery
{
    public function forRequest(Request $request): Builder
    {
        return KpiObjectiveInfo::query()
            ->with('objective')
            ->where('objective_id', $request->integer('oid'))
            ->orderBy('id');
    }
}
