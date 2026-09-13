<?php

namespace App\Queries;

use App\Models\Assessment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AppraisalListQuery
{
    public function forRequest(Request $request): Builder
    {
        return Assessment::query()
            // The score column totals each row, so the pieces it totals are
            // loaded with the page rather than one query per appraisal.
            ->with(['staff.team', 'position', 'reviewer', 'scores', 'template.bands'])
            ->when($request->filled('staff_id'), fn ($q) => $q->where('staff_id', $request->integer('staff_id')))
            // Team is the member's current team - an appraisal does not pin one.
            ->when($request->filled('team_id'), fn ($q) => $q->whereHas('staff', fn ($s) => $s->where('team_id', $request->integer('team_id'))))
            // Position is the one pinned to the appraisal, the role that was reviewed.
            ->when($request->filled('position_id'), fn ($q) => $q->where('position_id', $request->integer('position_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            // An appraisal covers a span, so "in this period" means the two
            // spans overlap - not that it starts inside the filter.
            ->when($request->filled('period_from'), fn ($q) => $q->whereDate('period_to', '>=', $request->date('period_from')))
            ->when($request->filled('period_to'), fn ($q) => $q->whereDate('period_from', '<=', $request->date('period_to')))
            ->latest('id');
    }
}
