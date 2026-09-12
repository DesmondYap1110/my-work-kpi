<?php

namespace App\Queries;

use App\Models\ProjectPhase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProjectPhaseListQuery
{
    public function forRequest(Request $request): Builder
    {
        return ProjectPhase::query()
            ->with(['project.team', 'files'])
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->integer('project_id')))
            ->when(
                $request->filled('team_id'),
                fn ($q) => $q->whereHas('project', fn ($p) => $p->where('team_id', $request->integer('team_id')))
            )
            ->when($request->filled('progress_status'), fn ($q) => $q->where('progress_status', $request->integer('progress_status')))
            ->latest('id');
    }
}
