<?php

namespace App\Queries;

use App\Models\ProjectTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProjectTaskListQuery
{
    public function forRequest(Request $request): Builder
    {
        return ProjectTask::query()
            ->with(['project', 'assignee', 'tag', 'parent'])
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->integer('project_id')))
            ->when($request->filled('assignee_id'), fn ($q) => $q->where('assignee_id', $request->integer('assignee_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->integer('status')))
            ->when($request->filled('due_from'), fn ($q) => $q->whereDate('due_date', '>=', $request->date('due_from')))
            ->when($request->filled('due_to'), fn ($q) => $q->whereDate('due_date', '<=', $request->date('due_to')))
            ->latest('id');
    }
}
