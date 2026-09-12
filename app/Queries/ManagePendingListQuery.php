<?php

namespace App\Queries;

use App\Models\ProjectKpi;
use Illuminate\Database\Eloquent\Builder;

class ManagePendingListQuery
{
    public function build(): Builder
    {
        return ProjectKpi::query()
            ->with(['staff', 'project', 'objectiveInfo'])
            ->pending()
            ->latest('submitted_at');
    }
}
