<?php

namespace App\Queries;

use App\Models\ProjectTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectTaskListQuery
{
    public function forRequest(Request $request): Builder
    {
        return ProjectTask::query()
            ->with(['project', 'assignee', 'tag'])
            ->tap(fn ($q) => $this->scopeToViewer($q))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->integer('project_id')))
            ->when($request->filled('assignee_id'), fn ($q) => $q->where('assignee_id', $request->integer('assignee_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->integer('status')))
            ->when($request->filled('due_from'), fn ($q) => $q->whereDate('due_date', '>=', $request->date('due_from')))
            ->when($request->filled('due_to'), fn ($q) => $q->whereDate('due_date', '<=', $request->date('due_to')))
            ->latest('id');
    }

    /**
     * An administrator sees every task; everybody else sees their own and
     * nothing more.
     *
     * This sits in the query rather than in the controller because the same
     * list is reached two ways - the page, and the AJAX endpoint that pages
     * it - and a restriction applied in only one of them is no restriction.
     * It is applied before the request's own filters, so an assignee_id sent
     * by hand narrows the result further but can never widen it.
     */
    private function scopeToViewer(Builder $query): void
    {
        $staff = Auth::user();

        if ($staff && ! $staff->isAdmin()) {
            $query->where('assignee_id', $staff->id);
        }
    }
}
