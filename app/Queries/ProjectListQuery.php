<?php

namespace App\Queries;

use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProjectListQuery
{
    public function forRequest(Request $request): Builder
    {
        return Project::query()
            ->when($request->filled('project_id'), fn ($q) => $q->where('id', $request->integer('project_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->integer('status')))
            ->when(
                $request->filled(['date_from', 'date_to']),
                fn ($q) => $q->spanningRange($request->date('date_from'), $request->date('date_to'))
            )
            ->latest('id');
    }
}
