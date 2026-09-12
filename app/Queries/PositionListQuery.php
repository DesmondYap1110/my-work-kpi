<?php

namespace App\Queries;

use App\Models\StaffPosition;
use Illuminate\Database\Eloquent\Builder;

class PositionListQuery
{
    public function build(): Builder
    {
        // withCount so StaffPosition::hasKpi() answers from the loaded count
        // instead of querying once per row.
        return StaffPosition::query()->withCount('objectives')->latest('id');
    }
}
