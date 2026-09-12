<?php

namespace App\Queries;

use App\Models\StaffPosition;
use Illuminate\Database\Eloquent\Builder;

class PositionListQuery
{
    public function build(): Builder
    {
        return StaffPosition::query()->latest('id');
    }
}
