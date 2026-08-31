<?php

namespace App\Queries;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;

class TeamListQuery
{
    public function build(): Builder
    {
        return Team::query()
            ->withCount(['staff' => fn ($q) => $q->active()])
            ->latest('team_id');
    }
}
