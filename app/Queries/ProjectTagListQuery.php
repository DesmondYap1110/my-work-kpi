<?php

namespace App\Queries;

use App\Models\ProjectTag;
use Illuminate\Database\Eloquent\Builder;

class ProjectTagListQuery
{
    public function build(): Builder
    {
        return ProjectTag::query()
            ->withCount('phases')
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
